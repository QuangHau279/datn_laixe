<?php

namespace App\Http\Controllers;

use App\Models\LoaiBangLai;
use App\Models\CauHoi;
use App\Models\CauHoiBangLai;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ThiController extends Controller
{
    /**
     * GET /api/thi/presets
     */
    public function presets()
    {
        $bangs = LoaiBangLai::where('active', 1)
            ->orderBy('id')
            ->get(['id', 'ten', 'socauhoi', 'mincauhoidung', 'active']);

        $presets = $bangs->mapWithKeys(function ($b) {
            $soCau    = (int) ($b->socauhoi ?: 25);
            $thoiGian = max(10, (int) ceil($soCau * 1.2));
            
            // Kiểm tra số bộ đề có sẵn cho hạng này
            $deAvailable = CauHoiBangLai::where('BangLaiId', $b->id)
                ->distinct()
                ->pluck('BoDe')
                ->filter(function($de) {
                    return is_numeric($de) && $de >= 1 && $de <= 5;
                })
                ->sort()
                ->values()
                ->toArray();
            
            // Luôn có đề ngẫu nhiên nếu có ít nhất 1 câu hỏi
            $totalQuestions = CauHoiBangLai::where('BangLaiId', $b->id)->count();
            $deOptions = $deAvailable; // [1, 2, 3, ...] nếu có
            if ($totalQuestions > 0) {
                $deOptions[] = 'RANDOM';
            }
            
            return [
                strtoupper(trim($b->ten)) => [
                    'so_cau'       => $soCau,
                    'thoi_gian'    => $thoiGian,
                    'dau_tu'       => (int) ($b->mincauhoidung ?: floor($soCau * 0.8)),
                    'min_cau_liet' => 0,
                    'de_options'   => $deOptions, // Chỉ trả về các bộ đề có sẵn
                    'total_questions' => $totalQuestions, // Tổng số câu hỏi của hạng
                ],
            ];
        });

        return response()->json([
            'presets'   => $presets,
            'loai_bang' => $bangs,
        ]);
    }

    /**
     * POST /api/thi/tao-de  body: { "hang": "B1" }
     * Trả về câu hỏi + đáp án (đã trộn nếu muốn) nhưng KHÔNG lộ đáp án đúng.
     */
    public function create(Request $request)
    {
        $request->validate([
            'hang' => 'required|string',
            'de'   => 'nullable',
        ]);
        $hang = strtoupper(trim($request->input('hang')));
        $de   = $request->input('de');

        // Tìm hạng chính xác trước
        $bang = LoaiBangLai::where('ten', $hang)
            ->where('active', 1)
            ->first();

        // Nếu không tìm thấy, thử tìm gần đúng
        if (!$bang) {
            $bang = LoaiBangLai::where('ten', 'like', "%{$hang}%")
                ->where('active', 1)
                ->first();
        }

        if (!$bang) {
            return response()->json([
                'message' => "Hạng '{$hang}' không hợp lệ. Vui lòng chọn lại hạng thi."
            ], 422);
        }
        
        // Lưu hạng thực tế được sử dụng
        $hang = strtoupper(trim($bang->ten));

        $soCau    = (int) ($bang->socauhoi ?: 25);
        $thoiGian = max(10, (int) ceil($soCau * 1.2));
        $dauTu    = (int) ($bang->mincauhoidung ?: floor($soCau * 0.8));

        // Nếu chọn một bộ đề cụ thể (1..5) thì lọc theo cột BoDe
        if (is_numeric($de)) {
            $idsTheoBang = CauHoiBangLai::where('BangLaiId', $bang->id)
                ->where('BoDe', (int)$de)
                ->pluck('CauHoiId');
        } else {
            $idsTheoBang = CauHoiBangLai::where('BangLaiId', $bang->id)->pluck('CauHoiId');
        }

        $query = CauHoi::where('active', 1)
            ->with(['hinhAnhs' => function ($q) {
                $q->where('active', 1)->orderBy('stt');
            }]);

        if ($idsTheoBang->count() > 0) {
            $query->whereIn('id', $idsTheoBang);
        }

        $all = $query->get(['id', 'stt', 'noidung', 'cauliet']);

        if ($all->count() === 0) {
            $message = is_numeric($de) 
                ? "Hạng {$hang} chưa có bộ đề {$de}. Vui lòng chọn bộ đề khác hoặc đề ngẫu nhiên."
                : "Hạng {$hang} chưa có câu hỏi. Vui lòng kiểm tra lại dữ liệu.";
            return response()->json(['message' => $message], 404);
        }

        // Tạo đề:
        // - Nếu de là RANDOM (hoặc null) ⇒ chọn ngẫu nhiên nhưng đảm bảo có ít nhất 1 câu liệt.
        // - Nếu de là 1..5 ⇒ theo bộ cố định, nếu không đủ thì lấy tất cả có trong bộ đề đó.
        if (!is_numeric($de)) {
            // Đề ngẫu nhiên: đảm bảo có ít nhất 1 câu liệt
            $liet = $all->where('cauliet', 1);
            if ($liet->count() > 0) {
                $oneLiet = $liet->random(1);
                $remain  = $all->whereNotIn('id', $oneLiet->pluck('id'))
                               ->shuffle()->take(max(0, $soCau - 1));
                $chon = $oneLiet->concat($remain)->shuffle();
            } else {
                $chon = $all->shuffle()->take($soCau);
            }
        } else {
            // Bộ đề cố định: lấy ngẫu nhiên từ bộ đề đó, nhưng không quá số câu có
            $chon = $all->shuffle()->take(min($soCau, $all->count()));
        }
        
        // Nếu không đủ câu, điều chỉnh số câu xuống số lượng thực tế có
        if ($chon->count() < $soCau) {
            // Nếu chọn bộ đề cố định mà không đủ câu
            if (is_numeric($de)) {
                $soCau = $chon->count();
                $thoiGian = max(10, (int) ceil($soCau * 1.2));
                $dauTu = (int) floor($soCau * 0.8);
            } else {
                // Nếu đề ngẫu nhiên mà không đủ → báo lỗi rõ ràng
                return response()->json([
                    'message' => "Không đủ câu hỏi để tạo đề. Hạng {$hang} chỉ có {$all->count()} câu hỏi, yêu cầu {$soCau} câu."
                ], 500);
            }
        }

        $payloadQuestions = [];
        $answerKey        = [];

        foreach ($chon as $q) {
            $answers = $q->dapAns()->get(['id','stt','noidung','caudung'])
                ->map(fn($a) => ['id'=>$a->id,'stt'=>$a->stt,'text'=>$a->noidung])
                ->values()->all();

            // Nếu muốn trộn đáp án hiển thị, bỏ comment dòng sau:
            // shuffle($answers);

            $imgs = $q->hinhAnhs()->get(['id','ten'])
                ->map(fn($h) => ['id'=>$h->id, 'ten'=>$h->ten, 'url'=>$h->url])
                ->values()->all();

            $correctIds = $q->dapAns()->where('caudung', 1)->pluck('id')->all();
            $answerKey[$q->id] = $correctIds;

            $payloadQuestions[] = [
                'id'      => $q->id,
                'stt'     => $q->stt,
                'text'    => $q->noidung,
                'is_liet' => (int)$q->cauliet === 1,
                'answers' => $answers,
                'images'  => $imgs,
            ];
        }

        $examId    = (string) Str::uuid();
        $expiresAt = Carbon::now()->addMinutes($thoiGian);

        session([
            "exams.$examId" => [
                'hang'         => $hang,
                'preset'       => [
                    'so_cau'    => $soCau,
                    'thoi_gian' => $thoiGian,
                    'dau_tu'    => $dauTu,
                ],
                'question_ids' => collect($payloadQuestions)->pluck('id')->all(),
                'answer_key'   => $answerKey,
                'liet_ids'     => $chon->where('cauliet', 1)->pluck('id')->all(),
                'expires_at'   => $expiresAt->toIso8601String(),
            ],
        ]);

        return response()->json([
            'exam_id'        => $examId,
            'hang'           => $hang,
            'expires_at'     => $expiresAt->toIso8601String(),
            'thoi_gian_phut' => $thoiGian,
            'so_cau'         => $soCau,
            'questions'      => $payloadQuestions,
        ]);
    }

    /**
     * POST /api/thi/nop-bai
     * body: { "exam_id":"...", "answers":[{"question_id":1,"answer_id":2}, ...] }
     */
    public function submit(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|string',
            'answers' => 'required|array',
        ]);

        $examId = $request->input('exam_id');
        $state  = session("exams.$examId");

        if (!$state) {
            return response()->json(['message' => 'Phiên thi không tồn tại hoặc đã hết hạn'], 410);
        }

        $expired = false;
        if (!empty($state['expires_at'])) {
            $expired = Carbon::parse($state['expires_at'])->isPast();
        }

        $answers   = collect($request->input('answers'));
        $answerKey = $state['answer_key'] ?? []; // {qid => [aid,...]}
        $lietIds   = $state['liet_ids'] ?? [];
        $preset    = $state['preset'] ?? ['dau_tu' => 0];

        // Map câu -> đáp án người dùng chọn (1 đáp án/câu)
        $mapUser = []; // {qid => aid}
        foreach ($answers as $ans) {
            $qid = (int) ($ans['question_id'] ?? 0);
            $aid = (int) ($ans['answer_id'] ?? 0);
            if ($qid > 0 && $aid > 0) {
                $mapUser[$qid] = $aid;
            }
        }

        $correctCount = 0;
        $wrong        = [];
        $lietWrong    = false;

        foreach ($answerKey as $qid => $correctIds) {
            $userAid   = $mapUser[$qid] ?? null;
            $isCorrect = $userAid && in_array($userAid, $correctIds, true);

            if ($isCorrect) {
                $correctCount++;
            } else {
                $wrong[] = (int) $qid;
                if (in_array($qid, $lietIds, true)) {
                    $lietWrong = true; // sai câu liệt ⇒ rớt
                }
            }
        }

        $total  = count($answerKey);
        $passed = ($correctCount >= (int) ($preset['dau_tu'] ?? 0)) && !$lietWrong;

        // Xoá session sau khi chấm (tuỳ nhu cầu có thể giữ lại để xem tiếp)
        session()->forget("exams.$examId");

        return response()->json([
            'passed'   => $passed,
            'reason'   => $passed ? null : ($lietWrong ? 'Sai câu liệt' : 'Không đủ số câu đúng tối thiểu'),
            'total'    => $total,
            'correct'  => $correctCount,
            'required' => (int) ($preset['dau_tu'] ?? 0),
            'wrong_question_ids' => $wrong,
            'liet_wrong' => $lietWrong,
            'expired'    => $expired,

            // === Thêm cho bảng xem lại ===
            'correct_map' => $answerKey, // map đáp án đúng
            'user_map'    => $mapUser,   // map đáp án người dùng đã chọn
        ]);
    }
}
