<?php

namespace Database\Factories;

use App\Models\CarrierType;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\InvoiceTrip;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceTrip>
 */
class InvoiceTripFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'origin_id' => Location::factory(),
            'driver_id' => Driver::factory(),
            'carrier_type_id' => CarrierType::factory(),
            'freight_fee' => fake()->randomFloat(2, 100, 10000),
            'weight' => fake()->optional()->numerify('##00 kg'),
            'sequence' => 1,
        ];
    }
}
