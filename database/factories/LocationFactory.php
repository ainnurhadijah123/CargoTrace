<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Port of '.fake()->unique()->city(),
            'code' => fake()->unique()->bothify('LOC-####'),
            'type' => fake()->randomElement(Location::TYPES),
            'country' => fake()->country(),
            'city' => fake()->city(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
