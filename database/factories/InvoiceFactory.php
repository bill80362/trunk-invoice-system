<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'year' => fake()->numberBetween(2024, 2026),
            'month' => fake()->numberBetween(1, 12),
            'invoice_number' => fake()->numerify('INV-####'),
            'issuer_name' => fake()->company(),
            'issuer_address' => fake()->address(),
            'issuer_phone' => fake()->phoneNumber(),
            'total_amount' => 0,
            'status' => 'draft',
            'confirmed_at' => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }
}
