<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\ReservationRepository;
use Carbon\Carbon;

class SeatController extends Controller
{
    private $reservationRepository;

    public function __construct(ReservationRepository $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    public function getSeatsStatus(Request $request)
    {
        $timeFilter = $request->query('timeFilter');
        $beginTime = isset($timeFilter['beginTime']) ? Carbon::parse($timeFilter['beginTime']) : null;
        $endTime = isset($timeFilter['endTime']) ? Carbon::parse($timeFilter['endTime']) : null;

        if (($beginTime && !$endTime) || (!$beginTime && $endTime)) {
            return response()->json(['error' => 'Both beginTime and endTime must be provided'], 400);
        }

        $now = Carbon::now();
        // TODO: Implement the opening hours logic
        // $isWeekend = $now->isWeekend();
        // $settings = DB::table('settings')->pluck('value', 'key');
        $openingHours = [
            'beginTime' => '08:00',
            'endTime' => '20:00',
        ];

        if (!$beginTime || !$endTime) {
            $beginTime = $now->copy()->setTimeFromTimeString($openingHours['beginTime']);
            $endTime = $now->copy()->setTimeFromTimeString($openingHours['endTime']);
            // dd($now);

            if ($now->greaterThanOrEqualTo($endTime)) {
                return response()->json(['status' => 'unavailable'], 422);
            } elseif ($now->between($beginTime, $endTime)) {
                $beginTime = $now->ceilMinute(30);
            }
        }

        $seats = DB::table('seats')->orderBy('id')->get();

        $seatData = [];
        foreach ($seats as $seat) {
            $seatData[$seat->id] = [
                'seatCode' => $seat->code,
                'status' => $seat->available ? 'available' : 'unavailable',
            ];
        }

        $reservations = $this->reservationRepository->getReservationsBetween($beginTime, $endTime);

        $seatCoverages = [];
        foreach ($reservations as $reservation) {
            $seatId = $reservation->seat_id;
            if (!isset($seatCoverages[$seatId])) {
                $seatCoverages[$seatId] = [];
            }
            $seatCoverages[$seatId][] = [
                'start' => Carbon::parse($reservation->begin_time),
                'end' => Carbon::parse($reservation->end_time),
            ];
        }

        foreach ($seatCoverages as $seatId => $coverages) {
            $currentEnd = $beginTime;
            foreach ($coverages as $timeRange) {
                if ($timeRange['start']->equalTo($currentEnd)) {
                    $currentEnd = $timeRange['end'];
                }
            }

            if ($currentEnd->greaterThanOrEqualTo($endTime)) {
                $seatData[$seatId]['status'] = 'reserved';
            } else {
                $seatData[$seatId]['status'] = 'partiallyReserved';
            }
        }

        return response()->json(array_values($seatData));
    }

    public function getSeatStatus($seatId)
    {
        // 檢查座位是否存在
        $seat = DB::table('seats')->where('id', $seatId)->first();
        if (!$seat) {
            return response()->json(['error' => 'Seat not found'], 404);
        }

        // 獲取該座位的所有預約
        $reservations = $this->reservationRepository->getReservationsBySeatId($seatId);

        // 整理預約時間段
        $timeSlots = $reservations->map(function ($reservation) {
            return [
                'beginTime' => $reservation->begin_time,
                'endTime' => $reservation->end_time,
            ];
        });

        // 返回座位預約狀態
        return response()->json($timeSlots, 200);
    }

    public function updateSeat(Request $request, $seatId)
    {
        $validatedData = $request->validate([
            'available' => 'required|boolean',
        ]);

        // 檢查座位是否存在
        $seat = DB::table('seats')->where('id', $seatId)->first();
        if (!$seat) {
            return response()->json(['error' => 'Seat not found'], 404);
        }

        // 更新座位資料
        $updated = DB::table('seats')
            ->where('id', $seatId)
            ->update([
                'available' => $validatedData['available'],
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return response()->json(['error' => 'Failed to update seat'], 400);
        }

        return response()->noContent();
    }
}
