<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CarrierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' Lines',
            'code' => strtoupper(fake()->unique()->lexify('????')),
            'contact_email' => fake()->safeEmail(),
            'phone' => fake()->numerify('+62##########'),
            'is_active' => true,
        ];
    }
}
