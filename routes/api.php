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
Route::get('/seats/{seatCode}', [SeatController::class, 'getSeatStatus']);
Route::put('/seats/{seatCode}', [SeatController::class, 'updateSeat']);
