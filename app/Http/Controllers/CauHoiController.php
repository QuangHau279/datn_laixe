<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class CauHoiController extends Controller
{
    // ======= ĐỔI TÊN BẢNG Ở ĐÂY CHO KHỚP DB CỦA BẠN =======
    private const TBL_QUESTIONS = 'tblbocauhoi'; // <-- nếu bảng bạn là 'tblcauhoi' thì đổi lại
    private const TBL_ANSWERS   = 'tblcautraloi';
    private const TBL_IMAGES    = 'tblhinhanh';
    private const IMG_ORDER_COL = 'st';               // cột sắp xếp ảnh (ảnh chụp của bạn là 'st', không phải 'stt')
    // ======================================================

    // /api/grid -> trả mảng STT để vẽ lưới
    public function grid()
    {
        return DB::table(self::TBL_QUESTIONS)->orderBy('stt')->pluck('stt');
    }

    // /api/cauhoi/{stt} -> 1 câu + đáp án + ảnh
    public function byStt($stt)
    {
        $q = DB::table(self::TBL_QUESTIONS)
            ->select('id','stt','noidung')
            ->where('stt',$stt)
            ->first();

        if (!$q) return response()->json(['message' => 'Not found'], 404);

        $answers = DB::table(self::TBL_ANSWERS)
            ->select('id','noidung','caudung')
            ->where('cauhoi_id', $q->id)
            ->orderBy('id')
            ->get()
            ->map(fn($a)=>[
                'id'      => $a->id,
                'noidung' => $a->noidung,
                'caudung' => (bool)$a->caudung,
            ]);

        $images = DB::table(self::TBL_IMAGES)
            ->select('ten')
            ->where('CauHoiId', $q->id)
            ->where('active', 1)
            ->orderBy(self::IMG_ORDER_COL)
            ->get()
            ->map(fn($im)=>[
                'url' => asset('images/cauhoi/'.$im->ten), // public/images/cauhoi/...
                'alt' => $im->ten,
            ]);

        return response()->json([
            'id'           => $q->id,
            'stt'          => $q->stt,
            'noidung'      => $q->noidung,
            'cau_tra_lois' => $answers,
            'images'       => $images,
        ]);
    }
}
