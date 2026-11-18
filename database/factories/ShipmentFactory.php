<?php

namespace Database\Factories;

use App\Enums\ShipmentStatus;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShipmentFactory extends Factory
{
    public function definition(): array
    {
        $departure = now()->addDays(fake()->numberBetween(1, 5));

        return [
            'shipment_number' => 'CT-TEST-'.fake()->unique()->numerify('######'),
            'customer_id' => User::factory(),
            'carrier_id' => Carrier::factory(),
            'origin_id' => Location::factory(),
            'destination_id' => Location::factory(),
            'created_by' => User::factory()->operator(),
            'cargo_type' => fake()->randomElement(['Electronics', 'Textiles', 'Machinery', 'Food']),
            'weight_kg' => fake()->randomFloat(2, 100, 20000),
            'volume_cbm' => fake()->randomFloat(2, 1, 60),
            'expected_departure_at' => $departure,
            'expected_arrival_at' => (clone $departure)->addDays(fake()->numberBetween(7, 25)),
            'current_status' => ShipmentStatus::Created,
        ];
    }
}
