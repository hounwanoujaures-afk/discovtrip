<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CompletePassedBookings extends Command
{
    protected $signature   = 'bookings:complete-passed';
    protected $description = 'Passe en completed les réservations confirmées dont la date est passée';

    public function handle(): void
    {
        $count = Booking::where('status', 'confirmed')
            ->whereDate('booking_date', '<', Carbon::today())
            ->update(['status' => 'completed']);

        $this->info("{$count} réservation(s) passée(s) en completed.");
    }
}
