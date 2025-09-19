<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CauHoiController;
use App\Http\Controllers\ThiController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/grid', [CauHoiController::class, 'grid']);
Route::get('/cauhoi/{stt}', [CauHoiController::class, 'byStt']);


Route::get('/thi/preset', [ThiController::class, 'presets']);
Route::post('/thi/tao-de', [ThiController::class, 'create']);
Route::post('/thi/nop-bai', [ThiController::class, 'submit']);
