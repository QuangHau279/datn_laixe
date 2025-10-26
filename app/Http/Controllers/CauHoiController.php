<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CauHoiController extends Controller
{
    private const TBL_QUESTIONS = 'tblbocauhoi';
    private const TBL_ANSWERS   = 'tblcautraloi';
    private const TBL_IMAGES    = 'tblhinhanh';

    // /api/grid -> trả danh sách số thứ tự để vẽ lưới
    public function grid()
    {
        $qc = Schema::hasColumn(self::TBL_QUESTIONS, 'stt') ? 'stt' : 'id';
        return DB::table(self::TBL_QUESTIONS)->orderBy($qc)->pluck($qc);
    }

    // /api/cauhoi/{stt} -> 1 câu + đáp án + ảnh
    public function byStt($stt)
    {
        // cột STT của bảng câu hỏi
        $qSttCol = Schema::hasColumn(self::TBL_QUESTIONS, 'stt') ? 'stt' : 'id';

        $q = DB::table(self::TBL_QUESTIONS)
            ->select('id', $qSttCol.' as stt', 'noidung')
            ->where($qSttCol, $stt)
            ->first();

        if (!$q) {
            return response()->json(['message' => 'Not found'], 404);
        }

        // --- ĐÁP ÁN ---
        // dò tên cột khóa ngoại
        $ansFk = Schema::hasColumn(self::TBL_ANSWERS, 'CauHoiId') ? 'CauHoiId'
               : (Schema::hasColumn(self::TBL_ANSWERS, 'cauhoi_id') ? 'cauhoi_id' : null);

        $ansQuery = DB::table(self::TBL_ANSWERS)->select('id','noidung','caudung');

        if ($ansFk) {
            $ansQuery->where($ansFk, $q->id);
        } else {
            // không có cột FK phù hợp -> trả rỗng để không 500
            $answers = collect();
            goto build_images;
        }

        // thứ tự đáp án: ưu tiên 'stt' nếu có
        if (Schema::hasColumn(self::TBL_ANSWERS, 'stt')) {
            $ansQuery->addSelect('stt')->orderBy('stt');
        } else {
            $ansQuery->orderBy('id');
        }

        $answers = $ansQuery->get()->map(function ($a) {
            return [
                'id'      => $a->id,
                'stt'     => $a->stt ?? null,
                'noidung' => $a->noidung,
                'caudung' => (bool)$a->caudung,
            ];
        });

        // --- ẢNH ---
        build_images:
        $imgFk = Schema::hasColumn(self::TBL_IMAGES, 'CauHoiId') ? 'CauHoiId'
               : (Schema::hasColumn(self::TBL_IMAGES, 'cauhoi_id') ? 'cauhoi_id' : null);

        $imgQuery = DB::table(self::TBL_IMAGES);

        if ($imgFk) {
            $imgQuery->where($imgFk, $q->id);
        } else {
            $images = collect();
            goto respond_json;
        }

        // cột tên file: ưu tiên 'path', sau đó 'ten'
        $hasPath = Schema::hasColumn(self::TBL_IMAGES, 'path');
        $hasTen  = Schema::hasColumn(self::TBL_IMAGES, 'ten');

        if ($hasPath) $imgQuery->addSelect('path');
        if ($hasTen)  $imgQuery->addSelect('ten');

        if (Schema::hasColumn(self::TBL_IMAGES, 'active')) {
            $imgQuery->where('active', 1);
        }

        // thứ tự ảnh: ưu tiên 'st', sau đó 'stt', cuối cùng 'id'
        if (Schema::hasColumn(self::TBL_IMAGES, 'st')) {
            $imgQuery->orderBy('st');
        } elseif (Schema::hasColumn(self::TBL_IMAGES, 'stt')) {
            $imgQuery->orderBy('stt');
        } else {
            $imgQuery->orderBy('id');
        }

        $images = $imgQuery->get()->map(function ($im) use ($hasPath, $hasTen) {
            $file = $hasPath ? ($im->path ?? '') : ($hasTen ? ($im->ten ?? '') : '');
            $file = ltrim((string)$file, '/\\');
            return [
                'url' => asset('images/cauhoi/'.$file), // bạn đang để ảnh ở public/images/cauhoi
                'alt' => $file,
            ];
        });

        // --- trả JSON ---
        respond_json:
        return response()->json([
            'id'           => $q->id,
            'stt'          => $q->stt,
            'noidung'      => $q->noidung,
            'cau_tra_lois' => $answers ?? [],
            'images'       => $images ?? [],
        ]);
    }
    public function search(\Illuminate\Http\Request $req)
{
    $q = trim((string)$req->query('q', ''));
    if ($q === '') {
        return response()->json(['items' => []]);
    }

    // Nếu gõ số -> trả về đúng câu đó (ưu tiên)
    if (ctype_digit($q)) {
        $stt = (int)$q;
        $row = \App\Models\CauHoi::query()
            ->select('id','stt','noi_dung')
            ->where('stt', $stt)
            ->first();

        return response()->json([
            'items' => $row ? [[
                'id'      => $row->id,
                'stt'     => $row->stt,
                'snippet' => \Illuminate\Support\Str::limit(strip_tags($row->noi_dung), 120),
            ]] : []
        ]);
    }

    // Tìm theo nội dung (và có thể mở rộng sang đáp án nếu bạn có bảng/field)
    $items = \App\Models\CauHoi::query()
        ->select('id','stt','noi_dung')
        ->where('noi_dung', 'like', '%'.$q.'%')
        ->orderBy('stt')
        ->limit(10)
        ->get()
        ->map(function($r) use ($q){
            $plain = strip_tags($r->noi_dung);
            // làm snippet ngắn, có chứa từ khóa
            $pos = mb_stripos($plain, $q);
            if ($pos === false) {
                $snippet = \Illuminate\Support\Str::limit($plain, 120);
            } else {
                $start = max(0, $pos - 25);
                $snippet = mb_substr($plain, $start, 120);
                if ($start > 0) $snippet = '…'.$snippet;
            }
            return [
                'id'      => $r->id,
                'stt'     => $r->stt,
                'snippet' => $snippet,
            ];
        })
        ->values();

    return response()->json(['items' => $items]);
}

}
