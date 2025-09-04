<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Http\Controllers\CauHoiController;

Route::get('/cauhoi', [CauHoiController::class, 'index']);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/cauhoi', function () {
    return view('cauhoi'); // resources/views/cauhoi.blade.php
});
