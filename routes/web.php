<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CauHoiController;
use App\Http\Controllers\HinhAnhController;
use App\Http\Controllers\ThiController;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\TrafficSignController;
use App\Http\Controllers\SimulationController;

/* ========= TRANG CƠ BẢN ========= */
Route::view('/', 'home.index')->name('home');
Route::get('/mo-phong', [SimulationController::class, 'index'])->name('simulation');
Route::get('/mo-phong/cau-hinh', [SimulationController::class, 'configPoints'])->name('simulation.config');
Route::view('/bien-bao', 'pages.bienbao')->name('bienbao');

/* ========= BIỂN BÁO GIAO THÔNG ========= */
Route::prefix('traffic-signs')->name('traffic-signs.')->group(function () {
    Route::get('/', [TrafficSignController::class, 'index'])->name('index');
    Route::get('/{slug}', [TrafficSignController::class, 'show'])->name('show');
});

Route::get('/thuc-hanh-lai-xe', [PracticeController::class, 'videosThucHanh'])
     ->name('videothuchanh');

/* Ôn tập riêng cho Xe máy (A1) */
Route::view('/on-tap-xe-may', 'pages.Ontapxemay')->name('xemay');


/* ========= ÔN TẬP 600 CÂU (CHUNG) =========
   /on-tap             : menu -> redirect vào /on-tap/cau-hoi
   /on-tap/cau-hoi     : trang ôn tập mặc định
   /on-tap/cau-hoi/{stt}: mở trực tiếp câu {stt}
*/
Route::get('/on-tap', fn () => redirect()->route('practice.cauhoi'))->name('practice');

Route::get('/on-tap/cau-hoi/{stt?}', function ($stt = null) {
    // Dùng view đang có: resources/views/cauhoi/cauhoi.blade.php
    return view('cauhoi.cauhoi', ['initialStt' => $stt]);
})->whereNumber('stt')->name('practice.cauhoi');

/* Giữ link cũ không gãy */
Route::redirect('/cau-hoi', '/on-tap/cau-hoi', 301);
Route::get('/cau-hoi/{stt}', fn ($stt) => redirect()->route('practice.cauhoi', ['stt' => $stt]))
    ->whereNumber('stt');
Route::redirect('/cauhoi', '/on-tap/cau-hoi', 301);


/* ========= API CHO ÔN TẬP ========= */
Route::prefix('api')->name('api.')->group(function () {
    Route::prefix('cau-hoi')->name('cauhoi.')->group(function () {
        Route::get('grid',          [CauHoiController::class, 'grid'])->name('grid');
        Route::get('{stt}',         [CauHoiController::class, 'byStt'])->whereNumber('stt')->name('byStt');
        Route::get('{id}/hinh-anh', [HinhAnhController::class, 'byId'])->whereNumber('id')->name('hinhanh');
    });

    /* ========= API THI THỬ ========= */
    Route::prefix('thi')->group(function () {
        Route::get('preset',   [ThiController::class, 'presets']);
        Route::post('tao-de',  [ThiController::class, 'create']);
        Route::post('nop-bai', [ThiController::class, 'submit']);
    });

    /* ========= API MÔ PHỎNG ========= */
    Route::prefix('simulation')->name('simulation.')->group(function () {
        Route::post('save-points', [SimulationController::class, 'savePoints'])->name('save-points');
        Route::get('video/{id}', [SimulationController::class, 'getVideo'])->whereNumber('id')->name('video');
    });
});


/* ========= TRANG THI THỬ ========= */
Route::view('/thi-thu', 'thi.thi')->name('thi.thu');
Route::get('/thi-thu/de/{id}', fn ($id) => view('thi.lamde', ['deId' => $id]))
    ->whereNumber('id')->name('thi.lamde');

Route::get('/chatbox', fn() => view('pages.chatbox'))->name('chatbox');