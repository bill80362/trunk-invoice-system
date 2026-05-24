<?php

namespace Database\Factories;

use App\Models\CarrierType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarrierType>
 */
class CarrierTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['整車', '拼車', '快遞', '零擔']),
        ];
    }
}
