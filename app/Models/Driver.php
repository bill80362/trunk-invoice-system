<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    protected $fillable = ['name', 'phone'];

    public function invoiceTrips(): HasMany
    {
        return $this->hasMany(InvoiceTrip::class);
    }
}
