<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MoPhong;

class MoPhongSeeder extends Seeder
{
    /**
     * Seed dữ liệu mẫu cho 5 video mô phỏng
     */
    public function run()
    {
        $videos = [
            [
                'stt' => 1,
                'video' => 'mophong1.mp4',
                'diem5' => 0,
                'diem4' => 0,
                'diem3' => 0,
                'diem2' => 0,
                'diem1' => 0,
                'diem1end' => 0,
                'active' => true,
            ],
            [
                'stt' => 2,
                'video' => 'mophong2.mp4',
                'diem5' => 0,
                'diem4' => 0,
                'diem3' => 0,
                'diem2' => 0,
                'diem1' => 0,
                'diem1end' => 0,
                'active' => true,
            ],
            [
                'stt' => 3,
                'video' => 'mophong3.mp4',
                'diem5' => 0,
                'diem4' => 0,
                'diem3' => 0,
                'diem2' => 0,
                'diem1' => 0,
                'diem1end' => 0,
                'active' => true,
            ],
            [
                'stt' => 4,
                'video' => 'mophong4.mp4',
                'diem5' => 0,
                'diem4' => 0,
                'diem3' => 0,
                'diem2' => 0,
                'diem1' => 0,
                'diem1end' => 0,
                'active' => true,
            ],
            [
                'stt' => 5,
                'video' => 'mophong5.mp4',
                'diem5' => 0,
                'diem4' => 0,
                'diem3' => 0,
                'diem2' => 0,
                'diem1' => 0,
                'diem1end' => 0,
                'active' => true,
            ],
        ];

        foreach ($videos as $video) {
            MoPhong::updateOrCreate(
                ['video' => $video['video']],
                $video
            );
        }

        $this->command->info('Đã seed ' . count($videos) . ' video mô phỏng!');
    }
}

