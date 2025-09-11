<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CauHoiController;
use App\Http\Controllers\HinhAnhController;

/**
 * ========== TRANG GIAO DIỆN ==========
 * /cau-hoi            : mở trang học
 * /cau-hoi/{stt}      : mở trang và tự load câu {stt} (deep-link)
 */
Route::get('/cau-hoi/{stt?}', function ($stt = null) {
    return view('cauhoi.cauhoi', ['initialStt' => $stt]); // đúng đường dẫn thư mục
})->whereNumber('stt')->name('page.cauhoi');

/**
 * ========== API JSON ==========
 * /api/cau-hoi/grid           : trả danh sách stt dùng cho lưới
 * /api/cau-hoi/{stt}          : trả dữ liệu 1 câu theo số thứ tự
 * /api/cau-hoi/{id}/hinh-anh  : trả hình ảnh của câu hỏi theo id
 */
Route::prefix('api')->name('api.')->group(function () {
    Route::prefix('cau-hoi')->name('cauhoi.')->group(function () {
        Route::get('grid',        [CauHoiController::class, 'grid'])->name('grid');
        Route::get('{stt}',       [CauHoiController::class, 'byStt'])->whereNumber('stt')->name('byStt');
        Route::get('{id}/hinh-anh',[HinhAnhController::class, 'byId'])->whereNumber('id')->name('hinhanh');
    });
});



// Các trang tĩnh

Route::view('/', 'home.index')->name('home');                 // Trang chủ
Route::view('/tro-giup', 'pages.help')->name('help');         // Menu item
Route::view('/on-tap', 'pages.practice')->name('practice');   // Menu item
Route::view('/mo-phong', 'pages.simulation')->name('simulation'); // Menu item
