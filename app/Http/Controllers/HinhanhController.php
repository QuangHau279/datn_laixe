<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HinhanhController extends Controller
{
    // Nếu CauHoiId = stt
    public function byStt(int $stt)
    {
        // Nếu cần kiểm tra tồn tại câu hỏi:
        // $exists = DB::table('tblcauhoi')->where('stt', $stt)->exists();
        // if (!$exists) abort(404);

        $rows = DB::table('tblhinhanh')
            ->where('CauHoiId', $stt)
            ->orderBy('stt')
            ->get();

        $urls = $rows->map(function ($r) {
            return [
                'ten' => $r->ten,
                'url' => asset('storage/cauhoi/'.$r->ten),
                'stt' => $r->stt,
                'active' => (int)$r->active,
            ];
        });

        return response()->json([
            'CauHoiId' => $stt,
            'images'   => $urls,
        ]);
    }

    // Nếu CauHoiId = id thật của tblcauhoi
    public function byId(int $id)
    {
        $rows = DB::table('tblhinhanh')
            ->where('CauHoiId', $id)
            ->orderBy('stt')
            ->get();

        $urls = $rows->map(fn($r) => [
            'ten' => $r->ten,
            'url' => asset('storage/cauhoi/'.$r->ten),
            'stt' => $r->stt,
            'active' => (int)$r->active,
        ]);

        return response()->json([
            'CauHoiId' => $id,
            'images'   => $urls,
        ]);
    }
}
