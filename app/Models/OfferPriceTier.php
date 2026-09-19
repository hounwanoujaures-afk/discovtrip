<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferPriceTier extends Model
{
    protected $fillable = [
        'offer_id',
        'min_participants',
        'max_participants',
        'price_per_person',
        'label',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
