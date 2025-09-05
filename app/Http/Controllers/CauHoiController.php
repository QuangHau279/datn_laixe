<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class CauHoiController extends Controller
{
    private const TBL_QUESTIONS = 'tblbocauhoi';
    private const TBL_ANSWERS   = 'tblcautraloi';
    private const TBL_IMAGES    = 'tblhinhanh';

    public function grid()
    {
        return DB::table(self::TBL_QUESTIONS)->orderBy('stt')->pluck('stt');
    }

    public function byStt($stt)
    {
        $q = DB::table(self::TBL_QUESTIONS)
            ->select('id','stt','noidung')
            ->where('stt', $stt)
            ->first();

        if (!$q) return response()->json(['message' => 'Not found'], 404);

        // FK chấp cả 'CauHoiId' và 'cauhoi_id'
        $answers = DB::table(self::TBL_ANSWERS)
            ->select('id','noidung','caudung','stt')
            ->where(function($w) use($q){
                $w->where('CauHoiId', $q->id)->orWhere('cauhoi_id', $q->id);
            })
            ->orderByRaw('COALESCE(stt, id)')
            ->get()
            ->map(fn($a) => [
                'id'      => $a->id,
                'stt'     => $a->stt,
                'noidung' => $a->noidung,
                'caudung' => (bool)$a->caudung,
            ]);

        // ẢNH: lấy tên file từ DB và build URL public/images/cauhoi/{file}
        $images = DB::table(self::TBL_IMAGES)
            ->select('ten','path','active','st','stt')
            ->where(function($w) use($q){
                $w->where('CauHoiId', $q->id)->orWhere('cauhoi_id', $q->id);
            })
            ->where('active', 1)
            ->orderByRaw('COALESCE(st, stt, id)')
            ->get()
            ->map(function ($im) {
                $file = $im->path ?: $im->ten;              // ưu tiên path, fallback ten
                $file = ltrim((string)$file, '/\\');        // chuẩn hóa
                return [
                    'url' => asset('images/cauhoi/'.$file), // KHÔNG dùng Storage
                    'alt' => $file,
                ];
            });

        return response()->json([
            'id'           => $q->id,
            'stt'          => $q->stt,
            'noidung'      => $q->noidung,
            'cau_tra_lois' => $answers,
            'images'       => $images,
        ]);
    }
}
