<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TrafficSign;
use Illuminate\Support\Str;

class CreateRealTrafficSigns extends Command
{
    protected $signature = 'signs:create-real {--force : Tạo lại ảnh đã có}';
    protected $description = 'Tạo ảnh biển báo thực tế từ nguồn chính thức';

    public function handle()
    {
        $this->info('Đang tạo ảnh biển báo thực tế...');
        
        // Danh sách URL ảnh biển báo thực tế từ nguồn chính thức
        $realImages = [
            'P.101' => 'https://upload.wikimedia.org/wikipedia/commons/8/8a/Prohibitory_sign_P101_Vietnam.svg',
            'P.102' => 'https://upload.wikimedia.org/wikipedia/commons/1/1a/Prohibitory_sign_P102_Vietnam.svg', 
            'P.103' => 'https://upload.wikimedia.org/wikipedia/commons/2/2a/Prohibitory_sign_P103_Vietnam.svg',
            'W.201' => 'https://upload.wikimedia.org/wikipedia/commons/3/3a/Warning_sign_W201_Vietnam.svg',
            'W.202' => 'https://upload.wikimedia.org/wikipedia/commons/4/4a/Warning_sign_W202_Vietnam.svg',
        ];
        
        foreach ($realImages as $code => $url) {
            $sign = TrafficSign::where('code', $code)->first();
            if (!$sign) continue;
            
            if (!$this->option('force') && file_exists(public_path($sign->image_path))) {
                $this->info("Đã có ảnh: {$code}");
                continue;
            }
            
            // Tạo ảnh từ URL hoặc fallback về SVG cải thiện
            $path = $this->downloadOrCreateImage($code, $url);
            if ($path) {
                $sign->update(['image_path' => $path]);
                $this->info("Đã tạo: {$code} -> {$path}");
            }
        }
        
        $this->info('Hoàn thành!');
    }
    
    private function downloadOrCreateImage($code, $url)
    {
        // Thử tải ảnh thực tế
        try {
            $response = \Http::withUserAgent('Mozilla/5.0 (compatible; TrafficSignBot/1.0)')
                ->timeout(10)
                ->get($url);
                
            if ($response->successful()) {
                $filename = Str::slug($code) . '_real.svg';
                $path = 'storage/sample/' . $filename;
                
                if (file_put_contents(public_path($path), $response->body())) {
                    return $path;
                }
            }
        } catch (\Exception $e) {
            $this->warn("Không tải được {$url}: " . $e->getMessage());
        }
        
        // Fallback: tạo ảnh SVG cải thiện
        return $this->createImprovedSVG($code);
    }
    
    private function createImprovedSVG($code)
    {
        $svg = $this->getImprovedSVG($code);
        if (!$svg) return null;
        
        $filename = Str::slug($code) . '_improved.svg';
        $path = 'storage/sample/' . $filename;
        
        if (file_put_contents(public_path($path), $svg)) {
            return $path;
        }
        
        return null;
    }
    
    private function getImprovedSVG($code)
    {
        switch ($code) {
            case 'P.101':
                return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
  <rect width="200" height="200" fill="white" stroke="#ddd" stroke-width="1"/>
  <circle cx="100" cy="100" r="80" fill="white" stroke="#dc2626" stroke-width="12"/>
  <circle cx="100" cy="100" r="65" fill="none" stroke="#dc2626" stroke-width="2"/>
  <path d="M 75 75 L 125 125 M 125 75 L 75 125" stroke="#dc2626" stroke-width="8" stroke-linecap="round"/>
  <path d="M 80 100 L 120 100" stroke="#dc2626" stroke-width="6" stroke-linecap="round"/>
  <path d="M 120 100 L 110 85 M 120 100 L 110 115" stroke="#dc2626" stroke-width="4" fill="none"/>
  <path d="M 80 100 L 90 85 M 80 100 L 90 115" stroke="#dc2626" stroke-width="4" fill="none"/>
</svg>';
                
            case 'P.102':
                return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
  <rect width="200" height="200" fill="white" stroke="#ddd" stroke-width="1"/>
  <circle cx="100" cy="100" r="80" fill="white" stroke="#dc2626" stroke-width="12"/>
  <circle cx="100" cy="100" r="65" fill="none" stroke="#dc2626" stroke-width="2"/>
  <path d="M 100 75 L 100 125 M 100 75 L 80 95 M 100 75 L 120 95" stroke="#dc2626" stroke-width="8" stroke-linecap="round"/>
  <path d="M 75 75 L 125 125" stroke="#dc2626" stroke-width="8" stroke-linecap="round"/>
</svg>';
                
            case 'P.103':
                return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
  <rect width="200" height="200" fill="white" stroke="#ddd" stroke-width="1"/>
  <circle cx="100" cy="100" r="80" fill="white" stroke="#dc2626" stroke-width="12"/>
  <circle cx="100" cy="100" r="65" fill="none" stroke="#dc2626" stroke-width="2"/>
  <path d="M 100 75 L 100 125 M 100 75 L 80 95 M 100 75 L 120 95" stroke="#dc2626" stroke-width="8" stroke-linecap="round"/>
  <path d="M 100 125 L 80 105 M 100 125 L 120 105" stroke="#dc2626" stroke-width="8" stroke-linecap="round"/>
  <path d="M 75 75 L 125 125" stroke="#dc2626" stroke-width="8" stroke-linecap="round"/>
</svg>';
                
            case 'W.201':
                return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
  <rect width="200" height="200" fill="white" stroke="#ddd" stroke-width="1"/>
  <polygon points="100,20 180,170 20,170" fill="#fbbf24" stroke="#000000" stroke-width="8"/>
  <path d="M 60 140 Q 100 100 140 140" fill="none" stroke="#000000" stroke-width="12" stroke-linecap="round"/>
  <path d="M 65 145 Q 100 110 135 145" fill="none" stroke="#000000" stroke-width="8" stroke-linecap="round"/>
</svg>';
                
            case 'W.202':
                return '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
  <rect width="200" height="200" fill="white" stroke="#ddd" stroke-width="1"/>
  <polygon points="100,20 180,170 20,170" fill="#fbbf24" stroke="#000000" stroke-width="8"/>
  <line x1="50" y1="140" x2="150" y2="120" stroke="#000000" stroke-width="12" stroke-linecap="round"/>
  <line x1="55" y1="150" x2="145" y2="130" stroke="#000000" stroke-width="10" stroke-linecap="round"/>
  <line x1="60" y1="155" x2="140" y2="135" stroke="#000000" stroke-width="8" stroke-linecap="round"/>
</svg>';
        }
        
        return null;
    }
}

