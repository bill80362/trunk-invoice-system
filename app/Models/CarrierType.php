<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarrierType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function invoiceTrips(): HasMany
    {
        return $this->hasMany(InvoiceTrip::class);
    }

    public function freightRates(): HasMany
    {
        return $this->hasMany(FreightRate::class);
    }
}
