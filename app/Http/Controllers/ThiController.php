<?php

namespace App\Http\Controllers;

use App\Models\LoaiBangLai;
use App\Models\CauHoi;
use App\Models\DapAn;
use App\Models\CauHoiBangLai;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class ThiController extends Controller
{
    // GET /api/thi/presets
    public function presets()
    {
        // Lấy trực tiếp từ bảng tblloaibanglai cho chắc
        $bangs = LoaiBangLai::where('active', 1)
                 ->orderBy('id')
                 ->get(['id','ten','socauhoi','mincauhoidung','active']);

        // thời gian mặc định theo số câu (có thể chỉnh theo ý bạn)
        $presets = $bangs->mapWithKeys(function ($b) {
            $soCau = (int)($b->socauhoi ?: 25);
            $thoiGian = max(10, (int)ceil($soCau * 1.2)); // ví dụ: ~1.2 phút / câu
            return [
                strtoupper(trim($b->ten)) => [
                    'so_cau'       => $soCau,
                    'thoi_gian'    => $thoiGian,
                    'dau_tu'       => (int)($b->mincauhoidung ?: floor($soCau * 0.8)),
                    'min_cau_liet' => 0, // tối giản: không ép số câu liệt tối thiểu
                ]
            ];
        });

        return response()->json([
            'presets'   => $presets,
            'loai_bang' => $bangs,
        ]);
    }

    // POST /api/thi/tao-de  body: { "hang": "B1" }
    public function create(Request $request)
    {
        $request->validate(['hang' => 'required|string']);
        $hang = strtoupper(trim($request->input('hang')));

        // tìm hạng theo cột 'ten'
        $bang = LoaiBangLai::where('ten', $hang)->orWhere('ten', 'like', "%{$hang}%")->first();
        if (!$bang) {
            return response()->json(['message' => 'Hạng không hợp lệ'], 422);
        }

        $soCau    = (int)($bang->socauhoi ?: 25);
        $thoiGian = max(10, (int)ceil($soCau * 1.2));
        $dauTu    = (int)($bang->mincauhoidung ?: floor($soCau * 0.8));

        // Lọc ngân hàng câu theo mapping (nếu có); nếu không thì lấy toàn bộ câu active
        $idsTheoBang = CauHoiBangLai::where('BangLaiId', $bang->id)->pluck('CauHoiId');
        $query = CauHoi::where('active', 1);
        if ($idsTheoBang->count() > 0) {
            $query->whereIn('id', $idsTheoBang);
        }
        $all = $query->get(['id','stt','noidung','cauliet']);

        if ($all->count() === 0) {
            return response()->json(['message' => 'Không có câu hỏi khả dụng'], 404);
        }

        // xáo trộn & lấy đủ số câu
        $chon = $all->shuffle()->take($soCau);
        if ($chon->count() < $soCau) {
            return response()->json(['message' => 'Không đủ câu hỏi để lập đề'], 500);
        }

        // chuẩn bị dữ liệu câu + đáp án (không trả đáp án đúng)
        $payloadQuestions = [];
        $answerKey = [];

        foreach ($chon as $q) {
            $answers = $q->dapAn()->get(['id','stt','noidung','caudung'])
                         ->map(fn($a) => ['id'=>$a->id,'stt'=>$a->stt,'text'=>$a->noidung])
                         ->values()->all();

            shuffle($answers); // trộn đáp án hiển thị

            // chỉ dùng đúng cột 'caudung = 1'
            $correctIds = $q->dapAn()->where('caudung', 1)->pluck('id')->all();
            $answerKey[$q->id] = $correctIds;

            $payloadQuestions[] = [
                'id'      => $q->id,
                'stt'     => $q->stt,
                'text'    => $q->noidung,
                'is_liet' => (int)$q->cauliet === 1,
                'answers' => $answers,
            ];
        }

        // lưu state vào session để chấm
        $examId = (string) Str::uuid();
        $expiresAt = Carbon::now()->addMinutes($thoiGian);

        session([
            "exams.$examId" => [
                'hang'          => $hang,
                'preset'        => ['so_cau'=>$soCau,'thoi_gian'=>$thoiGian,'dau_tu'=>$dauTu],
                'question_ids'  => collect($payloadQuestions)->pluck('id')->all(),
                'answer_key'    => $answerKey,
                'liet_ids'      => $chon->where('cauliet', 1)->pluck('id')->all(),
                'expires_at'    => $expiresAt->toIso8601String(),
            ]
        ]);

        return response()->json([
            'exam_id'       => $examId,
            'hang'          => $hang,
            'expires_at'    => $expiresAt->toIso8601String(),
            'thoi_gian_phut'=> $thoiGian,
            'so_cau'        => $soCau,
            'questions'     => $payloadQuestions,
        ]);
    }

    // POST /api/thi/nop-bai  body: { "exam_id":"...", "answers":[{"question_id":1,"answer_id":2}, ...] }
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

        $answers  = collect($request->input('answers'));
        $answerKey = $state['answer_key'] ?? [];
        $lietIds   = $state['liet_ids'] ?? [];
        $preset    = $state['preset'] ?? ['dau_tu' => 0];

        // map user answers
        $mapUser = [];
        foreach ($answers as $ans) {
            $qid = (int)$ans['question_id'];
            $aid = (int)$ans['answer_id'];
            $mapUser[$qid] = $aid;
        }

        $correctCount = 0;
        $wrong = [];
        $lietWrong = false;

        foreach ($answerKey as $qid => $correctIds) {
            $userAid = $mapUser[$qid] ?? null;
            $isCorrect = $userAid && in_array($userAid, $correctIds);
            if ($isCorrect) {
                $correctCount++;
            } else {
                $wrong[] = (int)$qid;
                if (in_array($qid, $lietIds, true)) {
                    $lietWrong = true; // sai câu liệt → rớt
                }
            }
        }

        $total  = count($answerKey);
        $passed = ($correctCount >= (int)($preset['dau_tu'] ?? 0)) && !$lietWrong;

        // xóa session
        session()->forget("exams.$examId");

        return response()->json([
            'passed'  => $passed,
            'reason'  => $passed ? null : ($lietWrong ? 'Sai câu liệt' : 'Không đủ số câu đúng tối thiểu'),
            'total'   => $total,
            'correct' => $correctCount,
            'required'=> (int)($preset['dau_tu'] ?? 0),
            'wrong_question_ids' => $wrong,
            'liet_wrong' => $lietWrong,
        ]);
    }
}
