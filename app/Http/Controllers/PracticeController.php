<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PracticeController extends Controller
{
    /**
     * Trang Thực hành lái xe.
     * - Ưu tiên lấy qua YouTube Data API v3 (có YT_API_KEY)
     * - Nếu lỗi/quota/thiếu key: fallback qua RSS (không cần key)
     * - Hỗ trợ ?v=VIDEO_ID để chọn video chính
     */
    public function videosThucHanh(Request $request)
    {
        $apiKey    = env('YT_API_KEY');
        $channelId = env('YT_CHANNEL_ID', 'UCXNeEDDDOo4MtAhRJPrNZqQ');

        // Lấy danh sách video: API trước, lỗi thì dùng RSS
        $items = $this->fetchFromApi($apiKey, $channelId);
        if (empty($items)) {
            $items = $this->fetchFromRss($channelId);
        }

        // Nếu vẫn rỗng -> báo thiếu cấu hình/không truy cập được
        if (empty($items)) {
            return view('pages.videothuchanh', [
                'error'  => 'Không lấy được danh sách video từ kênh (API và RSS đều lỗi).',
                'main'   => null,
                'others' => [],
                'keyword' => '',
                'totalResults' => 0,
            ]);
        }

        // Lấy keyword tìm kiếm từ query string
        $keyword = trim((string) $request->query('q', ''));
        
        // Filter videos theo keyword nếu có
        if ($keyword) {
            $items = collect($items)->filter(function ($item) use ($keyword) {
                $searchText = strtolower($item['title'] . ' ' . ($item['desc'] ?? ''));
                return str_contains($searchText, strtolower($keyword));
            })->values()->all();
        }

        // Chọn video chính theo ?v=... hoặc video mới nhất
        $queryId = trim((string) $request->query('v', ''));
        $main = $queryId ? collect($items)->firstWhere('id', $queryId) : ($items[0] ?? null);
        $others = collect($items)->reject(fn($v) => $main && $v['id'] === $main['id'])->values()->all();

        return view('pages.videothuchanh', [
            'error'  => null,
            'main'   => $main,
            'others' => $others,
            'keyword' => $keyword,
            'totalResults' => count($items),
        ]);
    }

    /** Ưu tiên: YouTube Data API v3 (cần API key) */
    private function fetchFromApi(?string $apiKey, string $channelId): array
    {
        if (!$apiKey) return [];

        return Cache::remember("yt.api.$channelId", now()->addMinutes(20), function () use ($apiKey, $channelId) {
            try {
                $resp = Http::timeout(8)->get('https://www.googleapis.com/youtube/v3/search', [
                    'key'        => $apiKey,
                    'channelId'  => $channelId,
                    'part'       => 'snippet',
                    'order'      => 'date',
                    'maxResults' => 24,
                    'type'       => 'video',
                ]);
                if (!$resp->ok()) return [];

                $arr = $resp->json('items') ?? [];
                return collect($arr)->map(function ($it) {
                    return [
                        'id'          => $it['id']['videoId'] ?? null,
                        'title'       => $it['snippet']['title'] ?? '',
                        'publishedAt' => $it['snippet']['publishedAt'] ?? null,
                        'thumb'       => $it['snippet']['thumbnails']['medium']['url'] ?? null,
                        'desc'        => $it['snippet']['description'] ?? '',
                    ];
                })->filter(fn($v) => !empty($v['id']))->values()->all();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    /** Fallback: YouTube RSS (không cần key) */
    private function fetchFromRss(string $channelId): array
    {
        return Cache::remember("yt.rss.$channelId", now()->addMinutes(20), function () use ($channelId) {
            try {
                $resp = Http::timeout(8)->get('https://www.youtube.com/feeds/videos.xml', [
                    'channel_id' => $channelId
                ]);
                if (!$resp->ok()) return [];

                $xml = @simplexml_load_string($resp->body());
                if (!$xml) return [];

                $ns = $xml->getNamespaces(true);
                $entries = $xml->entry ?? [];

                return collect($entries)->map(function ($e) use ($ns) {
                    $yt = $e->children($ns['yt']);
                    $id = (string) $yt->videoId;
                    return [
                        'id'          => $id,
                        'title'       => (string) $e->title,
                        'publishedAt' => (string) $e->published,
                        'thumb'       => "https://i.ytimg.com/vi/{$id}/mqdefault.jpg",
                        'desc'        => '',
                    ];
                })->filter(fn($v) => !empty($v['id']))->values()->all();
            } catch (\Throwable $e) {
                return [];
            }
        });
    }
}
