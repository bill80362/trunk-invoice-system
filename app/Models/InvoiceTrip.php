<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceTrip extends Model
{
    protected $fillable = [
        'invoice_id', 'date', 'origin_id', 'driver_id',
        'carrier_type_id', 'freight_fee', 'weight', 'sequence',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'freight_fee' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function carrierType(): BelongsTo
    {
        return $this->belongsTo(CarrierType::class);
    }

    public function invoiceTripStops(): HasMany
    {
        return $this->hasMany(InvoiceTripStop::class)->orderBy('sequence');
    }
}
