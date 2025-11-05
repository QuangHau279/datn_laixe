<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\TrafficSign;
use Illuminate\Support\Str;

class DownloadTrafficSignImages extends Command
{
    protected $signature = 'signs:download-images 
        {--code= : Chỉ tải cho 1 biển báo theo mã (vd: P.101)}
        {--url= : URL ảnh muốn dùng (ưu tiên nếu cung cấp)}
        {--dir=images/traffic-signs : Thư mục lưu ảnh trong public/}
        {--force : Tải lại nếu đã có ảnh}';
    protected $description = 'Tải ảnh biển báo từ URL bên ngoài và cập nhật image_path';

    public function handle()
    {
        $codeFilter = $this->option('code');
        $targetDir = trim($this->option('dir')) ?: 'images/traffic-signs';

        // Đảm bảo thư mục public/<dir> tồn tại
        File::ensureDirectoryExists(public_path($targetDir));

        $query = TrafficSign::query();
        if ($codeFilter) {
            $query->where('code', $codeFilter);
        }
        $signs = $query->get();

        if ($signs->isEmpty()) {
            $this->warn('Không tìm thấy biển báo nào để xử lý.');
            return Command::SUCCESS;
        }

        $downloaded = 0;

        foreach ($signs as $sign) {
            if (!$this->option('force') && $this->imageExists($sign->image_path)) {
                $this->info("Đã có ảnh: {$sign->code} -> {$sign->image_path}");
                continue;
            }

            // Ưu tiên URL từ tham số, nếu không có thì dùng source_url từ DB
            $imageUrl = $this->option('url') ?: ($sign->source_url ?: null);

            if (!$imageUrl) {
                $this->warn("Bỏ qua {$sign->code}: không có URL (chưa thiết lập source_url và không truyền --url)");
                continue;
            }

            $path = $this->downloadImage($imageUrl, $sign->code, $targetDir);
            if ($path) {
                $sign->update(['image_path' => $path]);
                $this->info("Đã tải: {$sign->code} -> {$path}");
                $downloaded++;
            } else {
                $this->error("Lỗi tải: {$sign->code}");
            }

            // Delay để tránh spam
            usleep(300000); // 0.3 giây
        }

        $this->info("Hoàn thành! Đã xử lý {$downloaded} ảnh.");
        return Command::SUCCESS;
    }
    
    private function imageExists($path)
    {
        if (!$path) return false;
        return file_exists(public_path($path)) || Storage::disk('public')->exists($path);
    }
    
    // getImageUrl no longer cần, giữ lại nếu muốn future mapping theo mã
    
    private function downloadImage($url, $code, string $targetDir)
    {
        try {
            $response = Http::withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
                ->timeout(30)
                ->get($url);
                
            if ($response->successful()) {
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
                // Làm sạch extension khi URL là dạng không có đuôi chuẩn
                $extension = strtolower(explode('?', $extension)[0]) ?: 'png';
                $filename = Str::slug((string) $code) . '.' . $extension;
                $publicRelativePath = trim($targetDir, '/\\') . '/' . $filename; // ví dụ: images/traffic-signs/p101.png

                File::put(public_path($publicRelativePath), $response->body());
                return $publicRelativePath; // lưu relative tới public để asset() dùng được
            }
        } catch (\Exception $e) {
            $this->error("Lỗi tải {$url}: " . $e->getMessage());
        }
        
        return null;
    }
}

