<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\SeatController;

Route::get("/seats", [SeatController::class, "getSeatsStatus"]);
Route::get('/seats/{seatId}', [SeatController::class, 'getSeatStatus']);
Route::put('/seats/{seatId}', [SeatController::class, 'updateSeat']);
