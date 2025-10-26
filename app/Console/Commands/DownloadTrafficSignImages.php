<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\TrafficSign;
use Illuminate\Support\Str;

class DownloadTrafficSignImages extends Command
{
    protected $signature = 'signs:download-images {--force : Tải lại ảnh đã có}';
    protected $description = 'Tải ảnh biển báo từ nguồn công khai';

    public function handle()
    {
        $signs = TrafficSign::all();
        $downloaded = 0;
        
        foreach ($signs as $sign) {
            if (!$this->option('force') && $this->imageExists($sign->image_path)) {
                $this->info("Đã có ảnh: {$sign->code}");
                continue;
            }
            
            $imageUrl = $this->getImageUrl($sign->code);
            if ($imageUrl) {
                $path = $this->downloadImage($imageUrl, $sign->code);
                if ($path) {
                    $sign->update(['image_path' => $path]);
                    $this->info("Đã tải: {$sign->code} -> {$path}");
                    $downloaded++;
                } else {
                    $this->error("Lỗi tải: {$sign->code}");
                }
            } else {
                // Tạo ảnh placeholder
                $path = $this->createPlaceholderImage($sign);
                if ($path) {
                    $sign->update(['image_path' => $path]);
                    $this->info("Tạo placeholder: {$sign->code}");
                    $downloaded++;
                }
            }
            
            // Delay để tránh spam
            usleep(500000); // 0.5 giây
        }
        
        $this->info("Hoàn thành! Đã xử lý {$downloaded} ảnh.");
    }
    
    private function imageExists($path)
    {
        if (!$path) return false;
        return file_exists(public_path($path)) || Storage::disk('public')->exists($path);
    }
    
    private function getImageUrl($code)
    {
        // Thử các nguồn ảnh công khai
        $sources = [
            "https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Prohibitory_sign_P101_Vietnam.svg/200px-Prohibitory_sign_P101_Vietnam.svg.png",
            "https://upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Prohibitory_sign_P102_Vietnam.svg/200px-Prohibitory_sign_P102_Vietnam.svg.png",
            "https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Prohibitory_sign_P103_Vietnam.svg/200px-Prohibitory_sign_P103_Vietnam.svg.png",
        ];
        
        // Map codes to specific URLs
        $urlMap = [
            'P.101' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8a/Prohibitory_sign_P101_Vietnam.svg/200px-Prohibitory_sign_P101_Vietnam.svg.png',
            'P.102' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/1a/Prohibitory_sign_P102_Vietnam.svg/200px-Prohibitory_sign_P102_Vietnam.svg.png',
            'P.103' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Prohibitory_sign_P103_Vietnam.svg/200px-Prohibitory_sign_P103_Vietnam.svg.png',
            'W.201' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/3a/Warning_sign_W201_Vietnam.svg/200px-Warning_sign_W201_Vietnam.svg.png',
            'W.202' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4a/Warning_sign_W202_Vietnam.svg/200px-Warning_sign_W202_Vietnam.svg.png',
        ];
        
        return $urlMap[$code] ?? null;
    }
    
    private function downloadImage($url, $code)
    {
        try {
            $response = Http::withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36')
                ->timeout(30)
                ->get($url);
                
            if ($response->successful()) {
                $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'png';
                $filename = Str::slug($code) . '.' . $extension;
                $path = 'sample/' . $filename;
                
                Storage::disk('public')->put($path, $response->body());
                return 'storage/' . $path;
            }
        } catch (\Exception $e) {
            $this->error("Lỗi tải {$url}: " . $e->getMessage());
        }
        
        return null;
    }
    
    private function createPlaceholderImage($sign)
    {
        // Tạo ảnh placeholder đơn giản bằng GD
        if (!extension_loaded('gd')) {
            $this->error("Extension GD không có sẵn để tạo ảnh placeholder");
            return null;
        }
        
        $width = 200;
        $height = 200;
        $image = imagecreatetruecolor($width, $height);
        
        // Màu nền
        $bgColor = imagecolorallocate($image, 240, 240, 240);
        imagefill($image, 0, 0, $bgColor);
        
        // Màu chữ
        $textColor = imagecolorallocate($image, 100, 100, 100);
        
        // Vẽ viền
        $borderColor = imagecolorallocate($image, 200, 200, 200);
        imagerectangle($image, 0, 0, $width-1, $height-1, $borderColor);
        
        // Vẽ text
        $text = $sign->code ?? 'N/A';
        $fontSize = 5;
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;
        
        imagestring($image, $fontSize, $x, $y, $text, $textColor);
        
        // Lưu ảnh
        $filename = Str::slug($sign->code ?? 'unknown') . '_placeholder.png';
        $path = 'sample/' . $filename;
        
        if (imagepng($image, storage_path('app/public/' . $path))) {
            imagedestroy($image);
            return 'storage/' . $path;
        }
        
        imagedestroy($image);
        return null;
    }
}

