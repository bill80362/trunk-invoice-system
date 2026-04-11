<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'client_id', 'year', 'month', 'invoice_number',
        'issuer_name', 'issuer_address', 'issuer_phone',
        'total_amount', 'status', 'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function invoiceTrips(): HasMany
    {
        return $this->hasMany(InvoiceTrip::class)->orderBy('sequence')->orderBy('date');
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function recalculateTotal(): void
    {
        $this->total_amount = $this->invoiceTrips()->sum('freight_fee');
        $this->save();
    }
}
