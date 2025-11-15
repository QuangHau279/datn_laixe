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

    // /api/xe-may/grid -> trả danh sách stt_250 của 250 câu xe máy
    public function gridXeMay()
    {
        $hasIn250 = Schema::hasColumn(self::TBL_QUESTIONS, 'in_250');
        $hasStt250 = Schema::hasColumn(self::TBL_QUESTIONS, 'stt_250');
        
        $query = DB::table(self::TBL_QUESTIONS);
        
        if ($hasIn250) {
            $query->where('in_250', 1);
        } elseif ($hasStt250) {
            $query->whereNotNull('stt_250');
        } else {
            // Fallback: nếu không có cột nào thì trả về rỗng
            return [];
        }
        
        if ($hasStt250) {
            // Lấy stt_250 và filter bỏ các giá trị null
            $results = $query->orderBy('stt_250')
                ->whereNotNull('stt_250')
                ->pluck('stt_250')
                ->filter(function($value) {
                    return $value !== null && $value !== '';
                })
                ->values()
                ->toArray();
            return $results;
        } else {
            // Nếu không có stt_250, trả về stt thông thường
            $qc = Schema::hasColumn(self::TBL_QUESTIONS, 'stt') ? 'stt' : 'id';
            return $query->orderBy($qc)->pluck($qc)->filter()->values()->toArray();
        }
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
            ->select('id','stt','noidung')
            ->where('stt', $stt)
            ->first();

        return response()->json([
            'items' => $row ? [[
                'id'      => $row->id,
                'stt'     => $row->stt,
                'snippet' => \Illuminate\Support\Str::limit(strip_tags($row->noidung), 120),
            ]] : []
        ]);
    }

    // Tìm theo nội dung (và có thể mở rộng sang đáp án nếu bạn có bảng/field)
    $items = \App\Models\CauHoi::query()
        ->select('id','stt','noidung')
        ->where('noidung', 'like', '%'.$q.'%')
        ->orderBy('stt')
        ->limit(10)
        ->get()
        ->map(function($r) use ($q){
            $plain = strip_tags($r->noidung);
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

    // /api/xe-may/search -> tìm kiếm câu hỏi xe máy theo số câu hoặc từ khóa
    public function searchXeMay(\Illuminate\Http\Request $req)
    {
        $q = trim((string)$req->query('q', ''));
        if ($q === '') {
            return response()->json(['items' => []]);
        }

        $hasIn250 = Schema::hasColumn(self::TBL_QUESTIONS, 'in_250');
        $hasStt250 = Schema::hasColumn(self::TBL_QUESTIONS, 'stt_250');
        
        $baseQuery = DB::table(self::TBL_QUESTIONS);
        
        // Chỉ lấy các câu trong 250 câu xe máy
        if ($hasIn250) {
            $baseQuery->where('in_250', 1);
        } elseif ($hasStt250) {
            $baseQuery->whereNotNull('stt_250');
        }

        // Nếu gõ số -> tìm theo stt_250 (ưu tiên)
        if (ctype_digit($q)) {
            $stt250 = (int)$q;
            
            $query = clone $baseQuery;
            if ($hasStt250) {
                $query->where('stt_250', $stt250);
            } else {
                // Nếu không có stt_250, tìm theo stt thông thường
                $qSttCol = Schema::hasColumn(self::TBL_QUESTIONS, 'stt') ? 'stt' : 'id';
                $query->where($qSttCol, $stt250);
            }
            
            $row = $query->select('id', 'stt', 'noidung', $hasStt250 ? 'stt_250' : DB::raw('NULL as stt_250'))
                ->first();

            if ($row) {
                $displayStt = ($hasStt250 && isset($row->stt_250) && $row->stt_250 !== null) 
                    ? $row->stt_250 
                    : ($row->stt ?? $row->id);
                    
                return response()->json([
                    'items' => [[
                        'id'      => $row->id,
                        'stt'     => $displayStt,
                        'stt_250' => $hasStt250 ? ($row->stt_250 ?? null) : null,
                        'snippet' => \Illuminate\Support\Str::limit(strip_tags($row->noidung), 120),
                    ]]
                ]);
            }
            
            return response()->json(['items' => []]);
        }

        // Tìm theo từ khóa trong nội dung
        $query = clone $baseQuery;
        $query->where('noidung', 'like', '%'.$q.'%');
        
        // Select các cột cần thiết
        $selectCols = ['id', 'noidung'];
        $qSttCol = Schema::hasColumn(self::TBL_QUESTIONS, 'stt') ? 'stt' : 'id';
        $selectCols[] = $qSttCol;
        if ($hasStt250) {
            $selectCols[] = 'stt_250';
        }
        
        $items = $query->select($selectCols)
            ->orderBy($hasStt250 ? 'stt_250' : $qSttCol)
            ->limit(20)
            ->get()
            ->map(function($r) use ($q, $hasStt250, $qSttCol) {
                $plain = strip_tags($r->noidung);
                
                // Làm snippet ngắn, có chứa từ khóa
                $pos = mb_stripos($plain, $q);
                if ($pos === false) {
                    $snippet = \Illuminate\Support\Str::limit($plain, 120);
                } else {
                    $start = max(0, $pos - 25);
                    $snippet = mb_substr($plain, $start, 120);
                    if ($start > 0) $snippet = '…'.$snippet;
                }
                
                $sttValue = $r->{$qSttCol} ?? null;
                $displayStt = ($hasStt250 && isset($r->stt_250) && $r->stt_250 !== null) 
                    ? $r->stt_250 
                    : ($sttValue ?? $r->id);
                
                return [
                    'id'      => $r->id,
                    'stt'     => $displayStt,
                    'stt_250' => $hasStt250 ? ($r->stt_250 ?? null) : null,
                    'snippet' => $snippet,
                ];
            })
            ->values();

        return response()->json(['items' => $items]);
    }

    // /api/xe-may/cau-hoi/{stt250} -> 1 câu xe máy theo stt_250 + đáp án + ảnh
    public function bySttXeMay($stt250)
    {
        $hasStt250 = Schema::hasColumn(self::TBL_QUESTIONS, 'stt_250');
        $hasIn250 = Schema::hasColumn(self::TBL_QUESTIONS, 'in_250');
        
        $query = DB::table(self::TBL_QUESTIONS);
        
        if ($hasStt250) {
            $query->where('stt_250', $stt250);
        } elseif ($hasIn250) {
            // Nếu không có stt_250, tìm theo stt thông thường nhưng phải có in_250 = 1
            $qSttCol = Schema::hasColumn(self::TBL_QUESTIONS, 'stt') ? 'stt' : 'id';
            $query->where($qSttCol, $stt250)->where('in_250', 1);
        } else {
            return response()->json(['message' => 'Not found'], 404);
        }
        
        // Select các cột cần thiết
        $selectCols = ['id', 'noidung'];
        $qSttCol = Schema::hasColumn(self::TBL_QUESTIONS, 'stt') ? 'stt' : 'id';
        $selectCols[] = $qSttCol;
        if ($hasStt250) {
            $selectCols[] = 'stt_250';
        }
        
        $q = $query->select($selectCols)->first();
        
        if (!$q) {
            return response()->json(['message' => 'Not found'], 404);
        }
        
        // Sử dụng stt_250 làm stt hiển thị nếu có
        $displayStt = ($hasStt250 && isset($q->stt_250) && $q->stt_250 !== null) 
            ? $q->stt_250 
            : (isset($q->stt) ? $q->stt : $q->id);
        
        // --- ĐÁP ÁN ---
        $ansFk = Schema::hasColumn(self::TBL_ANSWERS, 'CauHoiId') ? 'CauHoiId'
               : (Schema::hasColumn(self::TBL_ANSWERS, 'cauhoi_id') ? 'cauhoi_id' : null);
        
        $ansQuery = DB::table(self::TBL_ANSWERS)->select('id','noidung','caudung');
        
        if ($ansFk) {
            $ansQuery->where($ansFk, $q->id);
        } else {
            $answers = collect();
            goto build_images;
        }
        
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
        
        $hasPath = Schema::hasColumn(self::TBL_IMAGES, 'path');
        $hasTen  = Schema::hasColumn(self::TBL_IMAGES, 'ten');
        
        if ($hasPath) $imgQuery->addSelect('path');
        if ($hasTen)  $imgQuery->addSelect('ten');
        
        if (Schema::hasColumn(self::TBL_IMAGES, 'active')) {
            $imgQuery->where('active', 1);
        }
        
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
                'url' => asset('images/cauhoi/'.$file),
                'alt' => $file,
            ];
        });
        
        // --- trả JSON ---
        respond_json:
        return response()->json([
            'id'           => $q->id,
            'stt'          => $displayStt,
            'noidung'      => $q->noidung,
            'cau_tra_lois' => $answers ?? [],
            'images'       => $images ?? [],
        ]);
    }

}
