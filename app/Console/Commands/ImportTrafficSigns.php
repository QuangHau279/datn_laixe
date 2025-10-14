// app/Console/Commands/ImportTrafficSigns.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\{TrafficSign, TrafficSignCategory};
use Illuminate\Support\Str;

class ImportTrafficSigns extends Command
{
    protected $signature = 'signs:import 
        {--url=https://vi.wikipedia.org/wiki/Biển_báo_giao_thông_đường_bộ_Việt_Nam} 
        {--limit=0 : chỉ để debug, 0 = tất cả}';

    protected $description = 'Quét Wikipedia và nhập toàn bộ biển báo (ảnh + mô tả) vào DB';

    public function handle(): int
    {
        $url = $this->option('url');
        $html = Http::timeout(20)->get($url)->throw()->body();
        $crawler = new Crawler($html);

        // Tạo sẵn nhóm (mapping theo tiêu đề phần trên Wiki)
        $map = [
            'Biển báo cấm'        => ['slug'=>'cam',       'name'=>'Cấm'],
            'Biển báo nguy hiểm'  => ['slug'=>'nguy-hiem', 'name'=>'Nguy hiểm'],
            'Biển báo hiệu lệnh'  => ['slug'=>'hieu-lenh', 'name'=>'Hiệu lệnh'],
            'Biển báo chỉ dẫn'    => ['slug'=>'chi-dan',   'name'=>'Chỉ dẫn'],
            'Biển báo phụ'        => ['slug'=>'phu',       'name'=>'Phụ'],
        ];
        $catIds = [];
        foreach ($map as $k=>$v) {
            $catIds[$k] = TrafficSignCategory::firstOrCreate(['slug'=>$v['slug']], $v)->id;
        }

        // Mỗi section trên trang Wiki thường có tiêu đề <h2> + các bảng .wikitable
        $limit = (int)$this->option('limit');
        $count = 0;

        $crawler->filter('#mw-content-text h2')->each(function(Crawler $h2) use (&$count, $limit, $catIds) {
            $title = trim($h2->text());
            if (!isset($catIds[$title])) return;

            $categoryId = $catIds[$title];
            $section = $h2->nextAll()->reduce(function(Crawler $node) {
                // dừng khi gặp h2 tiếp theo
                return !in_array($node->nodeName(), ['h2']) ? true : false;
            });

            $section->filter('table.wikitable tr')->each(function(Crawler $tr) use ($categoryId, &$count, $limit) {
                // Bỏ hàng tiêu đề
                if ($tr->filter('th')->count() > 0) return;

                $imgNode = $tr->filter('td img');
                $textTd  = $tr->filter('td')->eq(1);

                if ($imgNode->count() === 0 || $textTd->count() === 0) return;

                $imgSrc = $imgNode->first()->attr('src') ?? '';
                if (!$imgSrc) return;

                // Chuẩn hoá link ảnh (//upload.wikimedia...)
                $imgSrc = Str::startsWith($imgSrc, '//') ? 'https:' . $imgSrc : $imgSrc;

                // Tên + mã (thường nằm trong <b>..</b> hoặc đầu câu)
                $raw = trim($textTd->html(''));
                $plain = trim(strip_tags($raw));
                // tách code dạng P.101, W.201a... nếu có
                preg_match('/([A-ZĐ]\.?[\.\d\w\-]+)/u', $plain, $m);
                $code = $m[1] ?? null;

                // tiêu đề = phần trước dấu chấm đầu
                $title = $plain;
                if (str_contains($plain, '.')) {
                    $p = explode('.', $plain, 2);
                    $title = trim($p[0]);
                }

                // mô tả = phần sau dấu chấm đầu
                $desc = $plain;
                if (isset($p[1])) $desc = trim($p[1]);

                // tải ảnh về
                try {
                    $bin = Http::timeout(20)->get($imgSrc)->throw()->body();
                    $ext = pathinfo(parse_url($imgSrc, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                    $fname = 'signs/'.date('Ymd').'/'.Str::slug($title).'-'.Str::random(6).'.'.$ext;
                    Storage::disk('public')->put($fname, $bin);
                    $imagePath = 'storage/'.$fname;
                } catch (\Throwable $e) {
                    $imagePath = $imgSrc; // fallback dùng link trực tiếp
                }

                TrafficSign::updateOrCreate(
                    ['title'=>$title, 'category_id'=>$categoryId],
                    [
                        'code'         => $code,
                        'description'  => $desc,
                        'image_path'   => $imagePath,
                        'source_url'   => $imgSrc,
                        'source_attrib'=> 'Wikipedia (CC BY-SA)'
                    ]
                );

                $count++;
                if ($limit && $count >= $limit) return false;
                return true;
            });
        });

        $this->info("Đã nhập xong ~{$count} biển báo.");
        $this->info("Nguồn: Wikipedia – vui lòng giữ attribution khi hiển thị.");
        return Command::SUCCESS;
    }
}
