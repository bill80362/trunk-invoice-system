<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceTripStop extends Model
{
    protected $fillable = ['invoice_trip_id', 'location_id', 'sequence'];

    public function invoiceTrip(): BelongsTo
    {
        return $this->belongsTo(InvoiceTrip::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
