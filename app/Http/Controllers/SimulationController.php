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
        
        // Lấy danh sách tất cả video mô phỏng
        $videos = MoPhong::where('active', true)
            ->orderBy('stt')
            ->get();

        // Chọn video chính: theo ?v=... hoặc video đầu tiên
        $mainVideo = null;
        if ($videoId) {
            $mainVideo = MoPhong::where('id', $videoId)
                ->where('active', true)
                ->first();
        }
        
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
        ]);
    }

    /**
     * API: Lưu điểm trừ của video
     */
    public function savePoints(Request $request)
    {
        $request->validate([
            'video_id' => 'required|exists:tblmophong,id',
            'diem5' => 'required|integer|min:0',
            'diem4' => 'required|integer|min:0',
            'diem3' => 'required|integer|min:0',
            'diem2' => 'required|integer|min:0',
            'diem1' => 'required|integer|min:0',
            'diem1end' => 'required|integer|min:0',
        ]);

        $video = MoPhong::findOrFail($request->video_id);
        
        $video->update([
            'diem5' => $request->diem5,
            'diem4' => $request->diem4,
            'diem3' => $request->diem3,
            'diem2' => $request->diem2,
            'diem1' => $request->diem1,
            'diem1end' => $request->diem1end,
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

