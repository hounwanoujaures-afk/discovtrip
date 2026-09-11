<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class PaymentController extends Controller
{
    // ══════════════════════════════════════════════════════
    // PAGE CHOIX PAIEMENT
    // ══════════════════════════════════════════════════════

    public function show(Request $request, string $reference)
    {
        $booking = $this->resolveBooking($reference, $request);

        // Déjà payé → page confirmation
        if ($booking->is_paid || $booking->payment_status === 'paid') {
            return redirect($this->bookingUrl($booking))
                ->with('success', 'Cette réservation est déjà réglée. ✓');
        }

        // Annulée → retour offre
        if (in_array($booking->status, ['cancelled_by_user', 'cancelled_by_partner'])) {
            return redirect()->route('offers.index')
                ->with('error', 'Cette réservation a été annulée.');
        }

        $kkiapayEnabled = ! empty(config('services.kkiapay.public_key'));
        $stripeEnabled  = ! empty(config('services.stripe.secret_key'));

        return view('pages.bookings.payment', compact(
            'booking',
            'kkiapayEnabled',
            'stripeEnabled'
        ));
    }

    // ══════════════════════════════════════════════════════
    // KKIAPAY — CALLBACK (vérification serveur post-paiement widget)
    // ══════════════════════════════════════════════════════

    /**
     * KKiaPay fonctionne avec un widget JS côté client.
     * Après succès du widget, le JS envoie le transactionId ici
     * via POST pour que le serveur vérifie avec le SDK PHP.
     */
    public function callbackKkiapay(Request $request, string $reference)
    {
        $booking = $this->resolveBooking($reference, $request);

        $transactionId = $request->input('transaction_id')
            ?? $request->query('transaction_id');

        if (! $transactionId) {
            Log::warning('KKiaPay callback: transaction_id manquant.', ['reference' => $reference]);
            return redirect(route('payment.show', $reference))
                ->with('error', 'Identifiant de transaction manquant.');
        }

        try {
            $kkiapay = new \Kkiapay\Kkiapay(
                config('services.kkiapay.public_key'),
                config('services.kkiapay.private_key'),
                config('services.kkiapay.secret'),
                config('services.kkiapay.sandbox', true)
            );

            $response = $kkiapay->verifyTransaction($transactionId);

            // Le SDK retourne un objet avec la propriété 'status'
            $status = $response->status ?? null;

            if ($status === 'SUCCESS') {
                $booking->update([
                    'payment_method'         => 'kkiapay',
                    'payment_status'         => 'paid',
                    'payment_reference'      => $transactionId,
                    'payment_transaction_id' => $transactionId,
                    'is_paid'                => true,
                    'paid_at'                => Carbon::now(),
                    'status'                 => 'confirmed',
                ]);

                $this->sendConfirmationEmail($booking);

                $redirectUrl = $this->bookingUrl($booking);

                // Requête AJAX depuis le widget JS → retourner JSON
                if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json(['redirect' => $redirectUrl]);
                }

                return redirect($redirectUrl)
                    ->with('success', '🎉 Paiement reçu ! Votre réservation est confirmée.');
            }

            // Transaction invalide ou non trouvée
            Log::warning('KKiaPay verify: statut inattendu.', [
                'status'     => $status,
                'reference'  => $reference,
                'transaction'=> $transactionId,
            ]);

            $booking->update(['payment_status' => 'failed']);

            $errorUrl = route('payment.show', $reference);

            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['error' => 'Statut : ' . ($status ?? 'inconnu'), 'redirect' => $errorUrl], 422);
            }

            return redirect($errorUrl)
                ->with('error', 'Paiement non confirmé (statut : ' . ($status ?? 'inconnu') . '). Contactez-nous si le montant a été débité.');

        } catch (\Exception $e) {
            Log::error('KKiaPay callback error: ' . $e->getMessage(), [
                'reference'    => $reference,
                'transaction'  => $transactionId,
            ]);

            return redirect($this->bookingUrl($booking))
                ->with('error', 'Erreur lors de la vérification du paiement. Contactez-nous si le montant a été débité.');
        }
    }

    // ══════════════════════════════════════════════════════
    // KKIAPAY — WEBHOOK (notifications serveur asynchrones)
    // ══════════════════════════════════════════════════════

    public function webhookKkiapay(Request $request)
    {
        // KKiaPay envoie un header x-kkiapay-secret pour sécuriser le webhook
        $receivedSecret = $request->header('x-kkiapay-secret');
        $expectedSecret = config('services.kkiapay.secret');

        if ($expectedSecret && $receivedSecret !== $expectedSecret) {
            Log::warning('KKiaPay webhook: secret invalide.');
            return response()->json(['error' => 'Invalid secret'], 401);
        }

        $payload = $request->all();
        $status  = $payload['status'] ?? null;
        $transactionId = $payload['transactionId'] ?? null;

        Log::info('KKiaPay webhook reçu.', ['status' => $status, 'transactionId' => $transactionId]);

        if ($status === 'SUCCESS' && $transactionId) {
            // Retrouver la réservation via le payment_reference stocké
            $booking = Booking::where('payment_reference', $transactionId)
                ->where('is_paid', false)
                ->first();

            if ($booking) {
                $booking->update([
                    'payment_status'         => 'paid',
                    'payment_transaction_id' => $transactionId,
                    'is_paid'                => true,
                    'paid_at'                => Carbon::now(),
                    'status'                 => 'confirmed',
                ]);

                $booking->load(['offer.city', 'tier', 'user']);
                $this->sendConfirmationEmail($booking);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    // ══════════════════════════════════════════════════════
    // STRIPE — INITIATION
    // ══════════════════════════════════════════════════════

    public function initStripe(Request $request, string $reference)
    {
        $booking = $this->resolveBooking($reference, $request);

        if (! config('services.stripe.secret_key')) {
            return back()->with('error', 'Le paiement par carte n\'est pas encore disponible. Veuillez réessayer plus tard.');
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret_key'));

            $clientEmail = $booking->guest_email ?? optional($booking->user)->email;

            $stripeCallbackBase = is_null($booking->user_id)
                ? URL::signedRoute('payment.stripe.callback', ['reference' => $reference])
                : route('payment.stripe.callback', $reference);

            $successUrl = $stripeCallbackBase
                . (str_contains($stripeCallbackBase, '?') ? '&' : '?')
                . 'session_id={CHECKOUT_SESSION_ID}';

            $cancelUrl = is_null($booking->user_id)
                ? URL::signedRoute('payment.show', ['reference' => $reference])
                : route('payment.show', $reference);

            $sessionData = [
                'payment_method_types' => ['card'],
                'mode'                 => 'payment',
                'success_url'          => $successUrl,
                'cancel_url'           => $cancelUrl,
                'metadata'             => [
                    'booking_reference' => $booking->reference,
                    'offer_id'          => $booking->offer_id,
                ],
                'line_items' => [[
                    'price_data' => [
                        'currency'     => 'eur',
                        'unit_amount'  => (int) round(($booking->total_price / 655.957) * 100),
                        'product_data' => [
                            'name'        => 'DiscovTrip — ' . $booking->offer->title,
                            'description' => $booking->participants . ' participant(s) · ' . Carbon::parse($booking->booking_date)->locale('fr')->isoFormat('D MMMM YYYY'),
                        ],
                    ],
                    'quantity' => 1,
                ]],
            ];

            if ($clientEmail) {
                $sessionData['customer_email'] = $clientEmail;
            }

            $session = \Stripe\Checkout\Session::create($sessionData);

            $booking->update([
                'payment_method'    => 'stripe',
                'payment_status'    => 'pending',
                'payment_reference' => $session->id,
            ]);

            return redirect($session->url);

        } catch (\Exception $e) {
            Log::error('Stripe init error: ' . $e->getMessage(), ['reference' => $reference]);
            return back()->with('error', 'Erreur lors de l\'initialisation du paiement. Veuillez réessayer.');
        }
    }

    // ══════════════════════════════════════════════════════
    // STRIPE — CALLBACK
    // ══════════════════════════════════════════════════════

    public function callbackStripe(Request $request, string $reference)
    {
        $booking = $this->resolveBooking($reference, $request);

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret_key'));

            $sessionId = $request->query('session_id');
            if (! $sessionId) {
                return redirect($this->bookingUrl($booking))
                    ->with('error', 'Session de paiement invalide.');
            }

            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $booking->update([
                    'payment_status'         => 'paid',
                    'payment_transaction_id' => $session->payment_intent,
                    'is_paid'                => true,
                    'paid_at'                => Carbon::now(),
                    'status'                 => 'confirmed',
                ]);

                $this->sendConfirmationEmail($booking);

                return redirect($this->bookingUrl($booking))
                    ->with('success', '🎉 Paiement reçu ! Votre réservation est confirmée.');
            }

            return redirect(route('payment.show', $reference))
                ->with('error', 'Paiement non finalisé. Veuillez réessayer.');

        } catch (\Exception $e) {
            Log::error('Stripe callback error: ' . $e->getMessage(), ['reference' => $reference]);
            return redirect($this->bookingUrl($booking))
                ->with('error', 'Erreur de vérification du paiement. Contactez-nous si le montant a été débité.');
        }
    }

    // ══════════════════════════════════════════════════════
    // STRIPE — WEBHOOK (événements asynchrones)
    // ══════════════════════════════════════════════════════

    public function webhookStripe(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature invalid.');
            return response()->json(['error' => 'Invalid signature'], 400);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook error'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session   = $event->data->object;
            $reference = $session->metadata->booking_reference ?? null;

            if ($reference) {
                $booking = Booking::where('reference', $reference)->first();
                if ($booking && ! $booking->is_paid) {
                    $booking->update([
                        'payment_status'         => 'paid',
                        'payment_transaction_id' => $session->payment_intent,
                        'is_paid'                => true,
                        'paid_at'                => Carbon::now(),
                        'status'                 => 'confirmed',
                    ]);

                    $booking->load(['offer.city', 'tier', 'user']);
                    $this->sendConfirmationEmail($booking);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    // ══════════════════════════════════════════════════════
    // HELPERS PRIVÉS
    // ══════════════════════════════════════════════════════

    private function resolveBooking(string $reference, Request $request): Booking
    {
        $query = Booking::where('reference', $reference)
            ->with(['offer.city', 'offer.activeTiers', 'tier', 'user']);

        if (auth()->check()) {
            return $query->where('user_id', auth()->id())->firstOrFail();
        }

        if ($request->hasValidSignature()) {
            return $query->whereNull('user_id')->firstOrFail();
        }

        return $query->firstOrFail();
    }

    private function bookingUrl(Booking $booking): string
    {
        if (is_null($booking->user_id)) {
            return URL::signedRoute('bookings.show', ['reference' => $booking->reference]);
        }
        return route('bookings.show', $booking->reference);
    }

    private function sendConfirmationEmail(Booking $booking): void
    {
        $email = $booking->guest_email ?? optional($booking->user)->email;
        if (! $email) return;

        try {
            Mail::to($email)->send(new BookingConfirmationMail($booking));
        } catch (\Exception $e) {
            Log::warning('Payment confirmation email failed: ' . $e->getMessage());
        }
    }
}