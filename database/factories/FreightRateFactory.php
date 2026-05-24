<?php

namespace Database\Factories;

use App\Models\CarrierType;
use App\Models\FreightRate;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FreightRate>
 */
class FreightRateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'origin_id' => Location::factory(),
            'destination_id' => Location::factory(),
            'carrier_type_id' => CarrierType::factory(),
            'base_price' => fake()->randomFloat(2, 100, 5000),
        ];
    }
}
