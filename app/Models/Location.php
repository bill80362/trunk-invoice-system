<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = ['name'];

    public function originTrips(): HasMany
    {
        return $this->hasMany(InvoiceTrip::class, 'origin_id');
    }

    public function tripStops(): HasMany
    {
        return $this->hasMany(InvoiceTripStop::class);
    }

    public function originRates(): HasMany
    {
        return $this->hasMany(FreightRate::class, 'origin_id');
    }

    public function destinationRates(): HasMany
    {
        return $this->hasMany(FreightRate::class, 'destination_id');
    }
}
