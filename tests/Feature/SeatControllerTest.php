<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Repositories\ReservationRepository;
use Tests\TestCase;
use Mockery;
use Carbon\Carbon;

class SeatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse("2024-11-25 09:00:00"));

        // 初始化測試數據
        DB::table('seats')->insert([
            [
                'id' => 1,
                'code' => 'A01',
                'available' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'code' => 'A02',
                'available' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'code' => 'B01',
                'available' => false,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);

        $mockedReservations = collect([
            (object) [
                'id' => 1,
                'seat_id' => 1,
                'begin_time' => '2024-11-25 09:00:00',
                'end_time' => '2024-11-25 10:00:00',
                'user_email' => 'user1@example.com',
            ],
            (object) [
                'id' => 2,
                'seat_id' => 2,
                'begin_time' => '2024-11-25 10:30:00',
                'end_time' => '2024-11-25 11:30:00',
                'user_email' => 'user2@example.com',
            ],
        ]);

        $mockedSeat1Reservations = collect([
            (object) [
                'id' => 1,
                'seat_id' => 1,
                'begin_time' => '2024-11-25 09:00:00',
                'end_time' => '2024-11-25 10:00:00',
                'user_email' => 'user1@example.com',
            ]
        ]);
        $mockedSeat3Reservations = collect([]);

        // Mock ReservationRepository
        $mock = Mockery::mock(ReservationRepository::class);
        $mock->shouldReceive('getReservationsBetween')
            ->andReturn($mockedReservations);

        $mock->shouldReceive('getReservationsBySeatId')
            ->with(1)
            ->andReturn($mockedSeat1Reservations);

        $mock->shouldReceive('getReservationsBySeatId')
            ->with(3)
            ->andReturn($mockedSeat3Reservations);

        $this->app->instance(ReservationRepository::class, $mock);
    }

    public function testGetSeatsStatusWithoutTimeFilter()
    {
        $response = $this->getJson('/api/seats');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'seatCode' => 'A01',
                'status' => 'partiallyReserved',
            ])
            ->assertJsonFragment([
                'seatCode' => 'A02',
                'status' => 'partiallyReserved',
            ])
            ->assertJsonFragment([
                'seatCode' => 'B01',
                'status' => 'unavailable',
            ]);
    }

    public function testGetSeatsStatusWithTimeFilter()
    {
        $query = 'timeFilter[beginTime]=2024-11-25T09:30:00&timeFilter[endTime]=2024-11-25T10:30:00';
        $response = $this->getJson("/api/seats?{$query}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'seatCode' => 'A01',
                'status' => 'partiallyReserved',
            ])
            ->assertJsonFragment([
                'seatCode' => 'A02',
                'status' => 'partiallyReserved',
            ])
            ->assertJsonFragment([
                'seatCode' => 'B01',
                'status' => 'unavailable',
            ]);
    }

    public function testGetSeatsStatusInvalidTimeFilter()
    {
        $response = $this->getJson('/api/seats?timeFilter[beginTime]=2024-11-25T09:30:00');

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Both beginTime and endTime must be provided',
            ]);
    }

    public function testGetSeatsStatusOutsideBusinessHours()
    {
        Carbon::setTestNow(Carbon::parse('2024-11-25 20:30:00'));
        $response = $this->getJson('/api/seats');

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'unavailable',
            ]);
    }

    public function testGetSeatStatusWithReservations()
    {
        // 發送請求
        $response = $this->getJson('/api/seats/1');

        // 驗證響應
        $response->assertStatus(200)
            ->assertJson([
                [
                    'beginTime' => '2024-11-25 09:00:00',
                    'endTime' => '2024-11-25 10:00:00',
                ],
            ]);
    }

    public function testGetSeatStatusWithNoReservations()
    {
        // 發送請求
        $response = $this->getJson('/api/seats/3');

        // 驗證響應
        $response->assertStatus(200)
            ->assertJson([]);
    }

    public function testGetSeatStatusWithInvalidSeatId()
    {
        // 發送請求
        $response = $this->getJson('/api/seats/999');

        // 驗證響應
        $response->assertStatus(404)
            ->assertJson(['error' => 'Seat not found']);
    }

    public function testUpdateSeatSuccessfully()
    {
        // 發送請求
        $response = $this->putJson('/api/seats/1', [
            'available' => false,
        ]);

        // 驗證響應
        $response->assertStatus(204);

        // 驗證資料庫
        $this->assertDatabaseHas('seats', [
            'id' => 1,
            'available' => false,
        ]);
    }

    public function testUpdateSeatWithInvalidSeatId()
    {
        // 發送請求
        $response = $this->putJson('/api/seats/999', [
            'available' => true,
        ]);

        // 驗證響應
        $response->assertStatus(404)
            ->assertJson(['error' => 'Seat not found']);
    }

    public function testUpdateSeatWithInvalidInput()
    {
        // 發送無效請求
        $response = $this->putJson('/api/seats/1', [
            'available' => 'invalid_boolean',
        ]);

        // 驗證響應
        $response->assertStatus(422);
    }
}
