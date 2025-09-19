<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CauHoiController;
use App\Http\Controllers\HinhAnhController;

/* ========= TRANG GIAO DIỆN CƠ BẢN ========= */
Route::view('/',          'home.index')->name('home');        // Trang chủ
Route::view('/tro-giup',  'pages.help')->name('help');         // Trợ giúp
Route::view('/mo-phong',  'pages.simulation')->name('simulation'); // Mô phỏng

/* ========= ÔN TẬP 600 CÂU =========
   /on-tap            : link menu -> chuyển vào trang câu hỏi
   /on-tap/cau-hoi    : trang ôn tập (mặc định)
   /on-tap/cau-hoi/{stt} : mở trực tiếp câu số {stt}
*/
Route::get('/on-tap', function () {
    return redirect()->route('practice.cauhoi');
})->name('practice');

Route::get('/on-tap/cau-hoi/{stt?}', function ($stt = null) {
    return view('pages.practice', ['initialStt' => $stt]);
})->whereNumber('stt')->name('practice.cauhoi');

/* ========= Giữ (và chuyển) LINK CŨ để không gãy =========
   /cau-hoi            -> /on-tap/cau-hoi
   /cau-hoi/{stt}      -> /on-tap/cau-hoi/{stt}
   /cauhoi             -> /on-tap/cau-hoi (alias)
*/
Route::redirect('/cau-hoi', '/on-tap/cau-hoi', 301);
Route::get('/cau-hoi/{stt}', function ($stt) {
    return redirect()->route('practice.cauhoi', ['stt' => $stt]);
})->whereNumber('stt');
Route::redirect('/cauhoi',  '/on-tap/cau-hoi', 301);

/* ========= API JSON =========
   /api/cau-hoi/grid
   /api/cau-hoi/{stt}
   /api/cau-hoi/{id}/hinh-anh
*/
Route::prefix('api')->name('api.')->group(function () {
    Route::prefix('cau-hoi')->name('cauhoi.')->group(function () {
        Route::get('grid',           [CauHoiController::class, 'grid'])->name('grid');
        Route::get('{stt}',          [CauHoiController::class, 'byStt'])->whereNumber('stt')->name('byStt');
        Route::get('{id}/hinh-anh',  [HinhAnhController::class, 'byId'])->whereNumber('id')->name('hinhanh');
    });
});

/* ========= THI THỬ =========
   /thi-thu            : link menu -> chuyển vào trang thi thử
   /thi-thu/de/{id}    : trang thi thử với đề đã tạo sẵn (id)
   /thi-thu/ket-qua/{id} : trang xem kết quả đề đã thi (id)
*/


Route::get('/thi-thu', function () {
    return view('thi.thi');
})->name('page.thi');
