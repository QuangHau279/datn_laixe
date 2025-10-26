<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Str;
use App\Models\TrafficSign;
use App\Models\TrafficSignCategory;

class ImportTrafficSigns extends Command
{
    protected $signature = 'signs:import
        {--title=Biển_báo_giao_thông_đường_bộ_Việt_Nam : Tiêu đề trang Wikipedia tiếng Việt}
        {--limit=0 : Nhập N bản ghi để test; 0 = tất cả}';

    protected $description = 'Quét Wikipedia (API) và nhập toàn bộ biển báo (ảnh + mô tả) vào DB';

    /** HTTP client có UA + retry (KHÔNG throw) */
    private function http()
    {
        // ĐỔI email này thành email liên hệ của bạn
        $ua = 'LyThuyetLaiXe.vn-Bot/1.0 (+mailto:you@example.com)';

        return Http::withUserAgent($ua)
            ->withHeaders([
                'Accept'          => 'application/json,text/html,*/*',
                'Accept-Language' => 'vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
            ])
            ->timeout(30)
            // ❗ Tham số thứ 4 = false: không tự throw khi thất bại
            ->retry(3, 500, null, false);
    }

    /** Lấy HTML: ưu tiên REST, nếu 404 thì Search API lấy title chuẩn, tiếp tục; cuối cùng fallback parse */
    private function fetchWikiHtml(string $title): ?string
    {
        $tryFetchRest = function (string $pageTitle): ?string {
            // space -> underscore, rồi percent-encode
            $encoded = rawurlencode(str_replace(' ', '_', $pageTitle));
            $restUrl = "https://vi.wikipedia.org/api/rest_v1/page/html/{$encoded}";

            $resp = $this->http()->get($restUrl);  // ❗ không throw
            if ($resp->successful()) {
                $html = trim($resp->body() ?? '');
                if ($html !== '') return $html;
            } else {
                \Log::warning('REST not successful', [
                    'url'    => $restUrl,
                    'status' => $resp->status(),
                ]);
            }
            return null;
        };

        $tryParseApi = function (string $pageTitle): ?string {
            $api  = 'https://vi.wikipedia.org/w/api.php';
            $resp = $this->http()->get($api, [
                'action'        => 'parse',
                'page'          => $pageTitle,   // chấp nhận Unicode/spaces
                'prop'          => 'text',
                'format'        => 'json',
                'formatversion' => 2,
                'redirects'     => 1,
                'utf8'          => 1,
            ]); // ❗ không throw

            if ($resp->successful()) {
                $json = $resp->json();
                if (!empty($json['parse']['text'])) {
                    return (string) $json['parse']['text'];
                }
                \Log::warning('Parse API missing text', ['page' => $pageTitle, 'json' => $json]);
            } else {
                \Log::warning('Parse API failed', ['status' => $resp->status(), 'page' => $pageTitle]);
            }
            return null;
        };

        // 1) REST với title gốc
        if ($html = $tryFetchRest($title)) return $html;

        // 2) Search API tìm tiêu đề chuẩn rồi thử lại
        $searchApi  = 'https://vi.wikipedia.org/w/api.php';
        $searchResp = $this->http()->get($searchApi, [
            'action'        => 'query',
            'list'          => 'search',
            'srsearch'      => $title,
            'srlimit'       => 1,
            'format'        => 'json',
            'formatversion' => 2,
            'utf8'          => 1,
        ]); // ❗ không throw
        if ($searchResp->successful()) {
            $js   = $searchResp->json();
            $best = $js['query']['search'][0]['title'] ?? null;
            if ($best) {
                if ($html = $tryFetchRest($best)) return $html;
                if ($html = $tryParseApi($best))  return $html;
            } else {
                \Log::warning('Search no result', ['query' => $title]);
            }
        } else {
            \Log::warning('Search API failed', ['status' => $searchResp->status(), 'query' => $title]);
        }

        // 3) Fallback cuối: parse với title gốc
        return $tryParseApi($title);
    }

    public function handle(): int
    {
        $title = $this->option('title') ?: 'Biển_báo_giao_thông_đường_bộ_Việt_Nam';
        $limit = (int) ($this->option('limit') ?? 0);

        $html = $this->fetchWikiHtml($title);
        if (!$html) {
            $this->error('Không lấy được nội dung trang từ API (REST/Search/Parse đều fail). Xem storage/logs/laravel.log.');
            return self::FAILURE;
        }

        $crawler = new Crawler($html);

        // Nhóm chính
        $map = [
            'Biển báo cấm'       => ['slug' => 'cam',       'name' => 'Cấm'],
            'Biển báo nguy hiểm' => ['slug' => 'nguy-hiem', 'name' => 'Nguy hiểm'],
            'Biển báo hiệu lệnh' => ['slug' => 'hieu-lenh', 'name' => 'Hiệu lệnh'],
            'Biển báo chỉ dẫn'   => ['slug' => 'chi-dan',   'name' => 'Chỉ dẫn'],
            'Biển báo phụ'       => ['slug' => 'phu',       'name' => 'Phụ'],
        ];
        $catIds = [];
        foreach ($map as $k => $v) {
            $catIds[$k] = TrafficSignCategory::firstOrCreate(['slug' => $v['slug']], $v)->id;
        }

        $count  = 0;
        $tables = $crawler->filter('table.wikitable');

        foreach ($tables as $tableEl) {
            $table = new Crawler($tableEl);

            // Tìm h2 gần nhất trước bảng để xác định nhóm
            $categoryName = null;
            $prev = $tableEl->previousSibling;
            while ($prev) {
                if ($prev->nodeType === XML_ELEMENT_NODE && strtolower($prev->nodeName) === 'h2') {
                    $h2 = new Crawler($prev);
                    $categoryName = trim($h2->text());
                    break;
                }
                $prev = $prev->previousSibling;
            }
            if (!$categoryName || !isset($catIds[$categoryName])) continue;

            $categoryId = $catIds[$categoryName];

            // Duyệt các hàng dữ liệu
            foreach ($table->filter('tr') as $trEl) {
                $tr = new Crawler($trEl);

                if ($tr->filter('th')->count() > 0) continue; // bỏ header

                $imgNode = $tr->filter('td img');
                $textTd  = $tr->filter('td')->eq(1);
                if ($imgNode->count() === 0 || $textTd->count() === 0) continue;

                $imgSrc = $imgNode->first()->attr('src') ?? '';
                if (!$imgSrc) continue;
                if (Str::startsWith($imgSrc, '//')) $imgSrc = 'https:' . $imgSrc;

                $plain = trim(strip_tags($textTd->html('')));
                preg_match('/([A-ZĐ]\.?[\.\d\w\-]+)/u', $plain, $m);
                $code = $m[1] ?? null;

                $titleText = $plain;
                $desc      = null;
                if (str_contains($plain, '.')) {
                    [$t, $rest] = explode('.', $plain, 2);
                    $titleText  = trim($t);
                    $desc       = trim($rest);
                }

                // Tải ảnh (throttle nhẹ)
                $imagePath = $imgSrc;
                try {
                    usleep(250 * 1000);
                    $bin = $this->http()
                        ->withHeaders(['Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8'])
                        ->get($imgSrc); // ❗ không throw
                    if ($bin->successful()) {
                        $ext   = pathinfo(parse_url($imgSrc, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                        $fname = 'signs/' . date('Ymd') . '/' . Str::slug($titleText) . '-' . Str::random(6) . '.' . $ext;
                        Storage::disk('public')->put($fname, $bin->body());
                        $imagePath = 'storage/' . $fname;
                    }
                } catch (\Throwable $e) {
                    // fallback: giữ URL gốc
                }

                TrafficSign::updateOrCreate(
                    ['title' => $titleText, 'category_id' => $categoryId],
                    [
                        'code'          => $code,
                        'description'   => $desc,
                        'image_path'    => $imagePath,
                        'source_url'    => $imgSrc,
                        'source_attrib' => 'Wikipedia (CC BY-SA)',
                    ]
                );

                $count++;
                if ($limit && $count >= $limit) {
                    $this->info("Đã nhập ~{$count} bản ghi (theo --limit). Nhóm cuối: {$categoryName}");
                    return self::SUCCESS;
                }
            }
        }

        $this->info("Đã nhập ~{$count} biển báo.");
        return self::SUCCESS;
    }
}
    