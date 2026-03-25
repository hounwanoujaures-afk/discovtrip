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

        $fedapayEnabled = ! empty(config('services.fedapay.secret_key'));
        $stripeEnabled  = ! empty(config('services.stripe.secret_key'));

        return view('pages.bookings.payment', compact(
            'booking',
            'fedapayEnabled',
            'stripeEnabled'
        ));
    }

    // ══════════════════════════════════════════════════════
    // FEDAPAY — INITIATION
    // ══════════════════════════════════════════════════════

    public function initFedapay(Request $request, string $reference)
    {
        $booking = $this->resolveBooking($reference, $request);

        if (! config('services.fedapay.secret_key')) {
            return back()->with('error', 'Le paiement Mobile Money n\'est pas encore disponible. Veuillez réessayer plus tard.');
        }

        try {
            \FedaPay\FedaPay::setApiKey(config('services.fedapay.secret_key'));
            \FedaPay\FedaPay::setEnvironment(config('services.fedapay.env', 'sandbox'));

            $clientEmail = $booking->guest_email ?? optional($booking->user)->email ?? 'client@discovtrip.com';
            $clientName  = $booking->guest_first_name
                ? trim($booking->guest_first_name . ' ' . $booking->guest_last_name)
                : optional($booking->user)?->name ?? 'Client DiscovTrip';

            $callbackUrl = is_null($booking->user_id)
                ? URL::signedRoute('payment.fedapay.callback', ['reference' => $reference])
                : route('payment.fedapay.callback', $reference);

            $transaction = \FedaPay\Transaction::create([
                'description' => 'DiscovTrip — ' . $booking->offer->title . ' (' . $booking->reference . ')',
                'amount'      => (int) $booking->total_price,
                'currency'    => ['iso' => 'XOF'],
                'callback_url'=> $callbackUrl,
                'customer'    => [
                    'firstname' => explode(' ', $clientName)[0],
                    'lastname'  => implode(' ', array_slice(explode(' ', $clientName), 1)) ?: 'Client',
                    'email'     => $clientEmail,
                    'phone_number' => [
                        'number'  => $booking->guest_phone ?? '22900000000',
                        'country' => 'BJ',
                    ],
                ],
            ]);

            $token = $transaction->generateToken();

            // Sauvegarder la référence transaction
            $booking->update([
                'payment_method'    => 'fedapay',
                'payment_status'    => 'pending',
                'payment_reference' => $transaction->id,
            ]);

            return redirect($token->url);

        } catch (\Exception $e) {
            Log::error('FedaPay init error: ' . $e->getMessage(), [
                'reference' => $reference,
                'trace'     => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Erreur lors de l\'initialisation du paiement. Veuillez réessayer.');
        }
    }

    // ══════════════════════════════════════════════════════
    // FEDAPAY — CALLBACK
    // ══════════════════════════════════════════════════════

    public function callbackFedapay(Request $request, string $reference)
    {
        $booking = $this->resolveBooking($reference, $request);

        try {
            \FedaPay\FedaPay::setApiKey(config('services.fedapay.secret_key'));
            \FedaPay\FedaPay::setEnvironment(config('services.fedapay.env', 'sandbox'));

            $transactionId = $request->query('id') ?? $booking->payment_reference;
            $transaction   = \FedaPay\Transaction::retrieve($transactionId);

            if ($transaction->status === 'approved') {
                $booking->update([
                    'payment_status'         => 'paid',
                    'payment_transaction_id' => $transaction->id,
                    'is_paid'                => true,
                    'paid_at'                => Carbon::now(),
                    'status'                 => 'confirmed',
                ]);

                $this->sendConfirmationEmail($booking);

                return redirect($this->bookingUrl($booking))
                    ->with('success', '🎉 Paiement reçu ! Votre réservation est confirmée.');
            }

            if ($transaction->status === 'declined') {
                $booking->update(['payment_status' => 'failed']);
                return redirect(route('payment.show', $reference))
                    ->with('error', 'Paiement refusé. Veuillez réessayer.');
            }

            // Statut inconnu ou en attente
            return redirect($this->bookingUrl($booking))
                ->with('info', 'Paiement en cours de traitement. Vous recevrez un email de confirmation.');

        } catch (\Exception $e) {
            Log::error('FedaPay callback error: ' . $e->getMessage(), ['reference' => $reference]);
            return redirect($this->bookingUrl($booking))
                ->with('error', 'Erreur lors de la vérification du paiement. Contactez-nous si le montant a été débité.');
        }
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

            // Les guests reçoivent des URLs signées — aucun email en clair
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
            $session  = $event->data->object;
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

    /**
     * Résoudre la réservation — fonctionne pour les connectés ET les invités
     */
    private function resolveBooking(string $reference, Request $request): Booking
    {
        $query = Booking::where('reference', $reference)
            ->with(['offer.city', 'offer.activeTiers', 'tier', 'user']);

        if (auth()->check()) {
            return $query->where('user_id', auth()->id())->firstOrFail();
        }

        // Guest : l'accès est autorisé uniquement si l'URL est signée
        if ($request->hasValidSignature()) {
            return $query->whereNull('user_id')->firstOrFail();
        }

        // Callbacks payment (FedaPay/Stripe) — le reference seul suffit côté serveur
        // car la route callback n'est pas exposée publiquement dans les emails
        return $query->firstOrFail();
    }

    /**
     * URL de confirmation après paiement.
     * Guests : URL signée (HMAC) — aucun email en clair dans l'URL.
     */
    private function bookingUrl(Booking $booking): string
    {
        if (is_null($booking->user_id)) {
            return URL::signedRoute('bookings.show', ['reference' => $booking->reference]);
        }
        return route('bookings.show', $booking->reference);
    }

    /**
     * Envoyer l'email de confirmation
     */
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