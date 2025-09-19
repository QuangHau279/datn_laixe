<?php

namespace App\Http\Controllers;

use App\Models\CauHoi;
use App\Models\CauTraLoi;
use App\Models\HinhAnh;
use App\Models\LoaiBangLai;
use App\Models\CauHoiBangLai;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ThiController extends Controller
{
    // GET /api/thi/preset
    public function presets()
    {
        $presets = config('thi.presets', []);
        // lấy danh sách hạng từ DB (nếu có)
        $bangs = LoaiBangLai::query()->orderBy('id')->get(['id','ma','ten']);
        return response()->json([
            'presets' => $presets,
            'loai_bang' => $bangs,
        ]);
    }

    // POST /api/thi/tao-de
    public function create(Request $request)
    {
        $request->validate([
            'hang' => 'required|string', // ví dụ 'B2'
        ]);

        $hang = strtoupper(trim($request->input('hang')));
        $presets = config('thi.presets', []);
        if (!isset($presets[$hang])) {
            return response()->json(['message' => 'Hạng không hợp lệ'], 422);
        }
        $preset = $presets[$hang];
        $soCau = (int)$preset['so_cau'];
        $thoiGian = (int)$preset['thoi_gian'];
        $minLiet = (int)($preset['min_cau_liet'] ?? 0);

        // Tìm BangLaiId theo 'ma' / 'ten' / 'code'
        $bang = LoaiBangLai::query()
            ->where('ma', $hang)
            ->orWhere('ten', $hang)
            ->orWhere('code', $hang)
            ->first();

        // Lấy danh sách câu theo hạng (nếu có mapping), nếu không fallback toàn bộ
        $queryCauTheoHang = CauHoi::query();

        if ($bang) {
            $ids = CauHoiBangLai::query()
                ->where('BangLaiId', $bang->id)
                ->pluck('CauHoiId');
            if ($ids->count() > 0) {
                $queryCauTheoHang->whereIn('id', $ids);
            }
        }

        $all = $queryCauTheoHang->get();

        if ($all->count() === 0) {
            return response()->json(['message' => 'Không có câu hỏi cho hạng '. $hang], 404);
        }

        // Chia câu liệt / không liệt
        $liet = $all->filter(fn($q) => $q->is_liet_normalized);
        $thuong = $all->reject(fn($q) => $q->is_liet_normalized);

        $chon = collect();

        // Đảm bảo có tối thiểu $minLiet câu liệt (nếu có)
        if ($minLiet > 0 && $liet->count() > 0) {
            $sl = min($minLiet, $liet->count());
            $chon = $chon->merge($liet->shuffle()->take($sl));
        }

        // Lấy phần còn lại từ pool chung
        $conLai = max(0, $soCau - $chon->count());
        $pool = $all->reject(fn($q) => $chon->contains('id', $q->id)); // loại trùng
        $chon = $chon->merge($pool->shuffle()->take($conLai));

        // Nếu vẫn chưa đủ vì thiếu dữ liệu
        if ($chon->count() < $soCau) {
            return response()->json(['message' => 'Không đủ câu hỏi để lập đề'], 500);
        }

        // Chuẩn bị dữ liệu câu + đáp án + ảnh (ẩn cờ correct)
        $payloadQuestions = [];
        $answerKey = []; // map question_id => [correct_answer_ids]

        foreach ($chon as $q) {
            $answers = $q->dapAn()->orderBy('stt')->get()->map(function ($a) {
                $text = $a->noi_dung ?? $a->ten ?? null;
                return [
                    'id' => $a->id,
                    'stt' => $a->stt,
                    'text' => $text,
                    // KHÔNG trả 'correct' cho client
                ];
            })->values()->toArray();

            // xáo trộn đáp án
            shuffle($answers);

            $correctIds = $q->dapAn()
                ->where(function ($qq) {
                    $qq->where('is_correct', 1)->orWhere('Dung', 1)->orWhere('isTrue', 1);
                })->pluck('id')->toArray();

            // nếu DB chỉ có 1 đáp án đúng (phổ biến), vẫn ok
            $answerKey[$q->id] = $correctIds;

            $images = $q->hinhAnh()->orderBy('stt')->get()->map(function ($img) {
                return [
                    'id'  => $img->id,
                    'stt' => $img->stt,
                    'ten' => $img->ten,
                    'url' => asset('storage/cauhoi/' . $img->ten),
                ];
            })->values()->toArray();

            $payloadQuestions[] = [
                'id'      => $q->id,
                'stt'     => $q->stt,
                'text'    => $q->text_normalized,
                'is_liet' => $q->is_liet_normalized, // client chỉ dùng để cảnh báo, không chấm
                'images'  => $images,
                'answers' => $answers,
            ];
        }

        // Tạo exam_id & lưu session state (để chấm nộp bài)
        $examId = (string) Str::uuid();
        $expiresAt = Carbon::now()->addMinutes($thoiGian);

        session([
            "exams.$examId" => [
                'hang' => $hang,
                'preset' => $preset,
                'question_ids' => collect($payloadQuestions)->pluck('id')->all(),
                'answer_key' => $answerKey,
                'liet_ids' => $chon->filter(fn($q) => $q->is_liet_normalized)->pluck('id')->all(),
                'expires_at' => $expiresAt->toIso8601String(),
                'created_at' => Carbon::now()->toIso8601String(),
            ]
        ]);

        return response()->json([
            'exam_id' => $examId,
            'hang' => $hang,
            'expires_at' => $expiresAt->toIso8601String(),
            'thoi_gian_phut' => $thoiGian,
            'so_cau' => $soCau,
            'questions' => $payloadQuestions,
        ]);
    }

    // POST /api/thi/nop-bai
    public function submit(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|string',
            'answers' => 'required|array', // [{question_id, answer_id}]
        ]);

        $examId = $request->input('exam_id');
        $state = session("exams.$examId");

        if (!$state) {
            return response()->json(['message' => 'Phiên thi không tồn tại hoặc đã hết hạn'], 410);
        }

        // Hết giờ thì vẫn cho nộp nhưng đánh dấu "nộp muộn"
        $expired = Carbon::now()->greaterThan(Carbon::parse($state['expires_at']));

        $answers = collect($request->input('answers'));
        $answerKey = $state['answer_key'] ?? [];
        $lietIds = $state['liet_ids'] ?? [];
        $preset = $state['preset'] ?? ['dau_tu' => 0];

        $correctCount = 0;
        $wrong = [];
        $lietWrong = false;

        // Chuẩn hóa answers map
        $mapUser = [];
        foreach ($answers as $ans) {
            $qid = (int)$ans['question_id'];
            $aid = (int)$ans['answer_id'];
            $mapUser[$qid] = $aid;
        }

        foreach ($answerKey as $qid => $correctIds) {
            $userAid = $mapUser[$qid] ?? null;
            // Nếu câu có nhiều đáp án đúng: coi đúng nếu user chọn 1 trong nhóm
            $isCorrect = $userAid && in_array($userAid, $correctIds);
            if ($isCorrect) {
                $correctCount++;
            } else {
                $wrong[] = (int)$qid;
                if (in_array($qid, $lietIds, true)) {
                    $lietWrong = true;
                }
            }
        }

        $total = count($answerKey);
        $passByScore = $correctCount >= (int)($preset['dau_tu'] ?? 0);
        $passed = $passByScore && !$lietWrong;

        // Dọn session sau khi nộp để tránh reuse
        session()->forget("exams.$examId");

        return response()->json([
            'passed' => (bool)$passed,
            'reason' => $passed ? null : ($lietWrong ? 'Sai câu liệt' : 'Không đủ số câu đúng tối thiểu'),
            'expired' => (bool)$expired,
            'total' => $total,
            'correct' => $correctCount,
            'required' => (int)($preset['dau_tu'] ?? 0),
            'wrong_question_ids' => $wrong,
            'liet_wrong' => (bool)$lietWrong,
        ]);
    }
}
