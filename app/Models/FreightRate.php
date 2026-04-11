<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreightRate extends Model
{
    protected $fillable = ['origin_id', 'destination_id', 'carrier_type_id', 'base_price'];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
        ];
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }

    public function carrierType(): BelongsTo
    {
        return $this->belongsTo(CarrierType::class);
    }
}
