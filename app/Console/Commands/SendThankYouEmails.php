<?php

namespace App\Console\Commands;

use App\Mail\BookingThankYouMail;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendThankYouEmails extends Command
{
    protected $signature   = 'bookings:send-thankyou';
    protected $description = 'Envoie un email de remerciement aux clients dont la visite est terminée hier';

    public function handle(): void
    {
        $bookings = Booking::where('status', 'completed')
            ->whereDate('booking_date', Carbon::yesterday())
            ->whereNull('thankyou_sent_at')
            ->with(['user', 'offer.city'])
            ->get();

        foreach ($bookings as $booking) {
            if (!$booking->user?->email) continue;

            Mail::to($booking->user->email)
                ->send(new BookingThankYouMail($booking));

            $booking->update(['thankyou_sent_at' => now()]);
            $this->info("Email envoyé à {$booking->user->email} — {$booking->reference}");
        }

        $this->info("{$bookings->count()} email(s) de remerciement envoyé(s).");
    }
}
