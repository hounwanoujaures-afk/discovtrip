<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ReviewController extends Controller
{
    public function store(Request $request, Booking $booking)
    {
        abort_if($booking->user_id !== auth()->id(), 403);
        abort_unless($booking->canReview(), 403, 'Cette réservation ne peut pas encore recevoir d\'avis.');

        $validated = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        Review::create([
            'offer_id'   => $booking->offer_id,
            'user_id'    => auth()->id(),
            'booking_id' => $booking->id,
            'rating'     => $validated['rating'],
            'comment'    => $validated['comment'],
            'status'     => 'pending',
        ]);

        Cache::forget('home.stats');

        return back()->with('success', 'Merci pour votre avis ! Il sera visible après validation par notre équipe.');
    }
}
