<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seat;

class SeatSeeder extends Seeder
{
    public function run()
    {
        Seat::factory()->count(139)->create(['type' => 'normal']);
        Seat::factory()->count(78)->create(['type' => 'notebook']);
    }
}
