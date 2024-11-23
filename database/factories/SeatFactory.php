<?php

namespace Database\Factories;

use App\Models\Seat;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeatFactory extends Factory
{
    protected $model = Seat::class;

    public function definition()
    {
        return [
            'type' => $this->faker->randomElement(['normal', 'notebook']),
            'available' => $this->faker->boolean,
        ];
    }
}
