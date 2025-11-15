<?php

namespace App\Http\Controllers;

use App\Models\MoPhong;
use Illuminate\Http\Request;

class SimulationController extends Controller
{
    /**
     * Hiển thị trang mô phỏng lý thuyết lái xe
     */
    public function index(Request $request)
    {
        $videoId = $request->query('v', null);
        $mode = $request->query('mode', 'practice'); // 'practice' hoặc 'test'
        
        // Lấy danh sách video mô phỏng
        if ($mode === 'test') {
            // Thi thử: Lấy ngẫu nhiên 10 video
            $videos = MoPhong::where('active', true)
                ->inRandomOrder()
                ->limit(10)
                ->get();
        } else {
            // Ôn tập: Lấy tất cả video theo thứ tự
            $videos = MoPhong::where('active', true)
                ->orderBy('stt')
                ->get();
        }

        // Chọn video chính: theo ?v=... hoặc video đầu tiên
        $mainVideo = null;
        if ($videoId) {
            // Kiểm tra xem video có trong danh sách hiện tại không
            $mainVideo = $videos->firstWhere('id', $videoId);
        }
        
        // Nếu không tìm thấy video trong danh sách, chọn video đầu tiên
        if (!$mainVideo && $videos->count() > 0) {
            $mainVideo = $videos->first();
        }

        // Lấy danh sách video khác
        $otherVideos = $videos->reject(function ($video) use ($mainVideo) {
            return $mainVideo && $video->id === $mainVideo->id;
        });

        return view('pages.simulation', [
            'mainVideo' => $mainVideo,
            'otherVideos' => $otherVideos,
            'allVideos' => $videos,
            'mode' => $mode,
        ]);
    }

    /**
     * API: Lưu điểm trừ của video
     */
    public function savePoints(Request $request)
    {
        $request->validate([
            'video_id' => 'required|exists:tblmophong,id',
            'diem5' => 'nullable|numeric|min:0',
            'diem4' => 'nullable|numeric|min:0',
            'diem3' => 'nullable|numeric|min:0',
            'diem2' => 'nullable|numeric|min:0',
            'diem1' => 'nullable|numeric|min:0',
            'diem1end' => 'nullable|numeric|min:0',
        ]);

        $video = MoPhong::findOrFail($request->video_id);
        
        $video->update([
            'diem5' => $request->diem5 ?? 0.0,
            'diem4' => $request->diem4 ?? 0.0,
            'diem3' => $request->diem3 ?? 0.0,
            'diem2' => $request->diem2 ?? 0.0,
            'diem1' => $request->diem1 ?? 0.0,
            'diem1end' => $request->diem1end ?? 0.0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu điểm trừ thành công',
            'data' => $video,
        ]);
    }

    /**
     * API: Lấy thông tin video
     */
    public function getVideo($id)
    {
        $video = MoPhong::where('id', $id)
            ->where('active', true)
            ->firstOrFail();

        return response()->json($video);
    }

    /**
     * Trang cấu hình điểm trừ cho video (Admin)
     */
    public function configPoints(Request $request)
    {
        $videoId = $request->query('v', null);
        
        // Lấy danh sách tất cả video
        $videos = MoPhong::where('active', true)
            ->orderBy('stt')
            ->get();

        // Chọn video cần cấu hình
        $mainVideo = null;
        if ($videoId) {
            $mainVideo = MoPhong::where('id', $videoId)
                ->where('active', true)
                ->first();
        }
        
        if (!$mainVideo && $videos->count() > 0) {
            $mainVideo = $videos->first();
        }

        return view('pages.simulation-config', [
            'mainVideo' => $mainVideo,
            'allVideos' => $videos,
        ]);
    }
}

