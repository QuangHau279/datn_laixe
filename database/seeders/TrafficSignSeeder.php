<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrafficSignCategory;
use App\Models\TrafficSign;

class TrafficSignSeeder extends Seeder
{
    public function run()
    {
        // Tạo các danh mục biển báo
        $categories = [
            ['slug' => 'cam', 'name' => 'Cấm'],
            ['slug' => 'nguy-hiem', 'name' => 'Nguy hiểm'],
            ['slug' => 'hieu-lenh', 'name' => 'Hiệu lệnh'],
            ['slug' => 'chi-dan', 'name' => 'Chỉ dẫn'],
            ['slug' => 'phu', 'name' => 'Phụ'],
        ];

        foreach ($categories as $category) {
            TrafficSignCategory::firstOrCreate(['slug' => $category['slug']], $category);
        }

        // Tạo dữ liệu mẫu cho biển báo cấm
        $camCategory = TrafficSignCategory::where('slug', 'cam')->first();
        $camSigns = [
            [
                'code' => 'P.101',
                'title' => 'Cấm đi ngược chiều',
                'description' => 'Cấm các phương tiện đi ngược chiều',
                'image_path' => null,
                'source_url' => 'https://example.com',
                'source_attrib' => 'Sample data'
            ],
            [
                'code' => 'P.102',
                'title' => 'Cấm rẽ trái',
                'description' => 'Cấm rẽ trái tại vị trí này',
                'image_path' => null,
                'source_url' => 'https://example.com',
                'source_attrib' => 'Sample data'
            ],
            [
                'code' => 'P.103',
                'title' => 'Cấm rẽ phải',
                'description' => 'Cấm rẽ phải tại vị trí này',
                'image_path' => null,
                'source_url' => 'https://example.com',
                'source_attrib' => 'Sample data'
            ]
        ];

        foreach ($camSigns as $sign) {
            TrafficSign::firstOrCreate(
                ['code' => $sign['code'], 'category_id' => $camCategory->id],
                array_merge($sign, ['category_id' => $camCategory->id])
            );
        }

        // Tạo dữ liệu mẫu cho biển báo nguy hiểm
        $nguyHiemCategory = TrafficSignCategory::where('slug', 'nguy-hiem')->first();
        $nguyHiemSigns = [
            [
                'code' => 'W.201',
                'title' => 'Đường cong nguy hiểm',
                'description' => 'Báo hiệu đường cong nguy hiểm phía trước',
                'image_path' => null,
                'source_url' => 'https://example.com',
                'source_attrib' => 'Sample data'
            ],
            [
                'code' => 'W.202',
                'title' => 'Đường dốc nguy hiểm',
                'description' => 'Báo hiệu đường dốc nguy hiểm',
                'image_path' => null,
                'source_url' => 'https://example.com',
                'source_attrib' => 'Sample data'
            ]
        ];

        foreach ($nguyHiemSigns as $sign) {
            TrafficSign::firstOrCreate(
                ['code' => $sign['code'], 'category_id' => $nguyHiemCategory->id],
                array_merge($sign, ['category_id' => $nguyHiemCategory->id])
            );
        }
    }
}
