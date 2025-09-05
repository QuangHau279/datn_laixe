<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CauHoiController;

// Trang giao diện
Route::get('/cau-hoi', fn() => view('cauhoi'))->name('cauhoi.page');
Route::get('/cauhoi',  fn() => view('cauhoi')); // alias

// API JSON (JS chỉ load 1 câu/lần)
Route::get('/api/grid',         [CauHoiController::class, 'grid']);
Route::get('/api/cauhoi/{stt}', [CauHoiController::class, 'byStt'])->whereNumber('stt');

// Trang mặc định
Route::get('/', fn () => view('welcome'));
