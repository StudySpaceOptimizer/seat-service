<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservationRepository
{
    /**
     * 根據開始與結束時間獲取 reservations 資料
     *
     * @param Carbon $beginTime 開始時間
     * @param Carbon $endTime 結束時間
     * @return \Illuminate\Support\Collection
     */
    public function getReservationsBetween(Carbon $beginTime, Carbon $endTime)
    {
        return DB::table('reservations')
            ->where('begin_time', '>=', $beginTime->toDateTimeString())
            ->where('end_time', '<=', $endTime->toDateTimeString())
            ->orderBy('seat_id')
            ->orderBy('begin_time')
            ->get();
    }

    public function getReservationsBySeatId(int $seatId)
    {
        return DB::table('reservations')
            ->where('seat_id', $seatId)
            ->orderBy('begin_time', 'asc')
            ->get(['begin_time', 'end_time']);
    }
}
