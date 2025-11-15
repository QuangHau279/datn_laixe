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

        // ========== BIỂN BÁO CẤM (P Series) ==========
        // NOTE: Cần thêm hình ảnh cho các biển báo sau:
        // P.101, P.102, P.103, P.104, P.105, P.106, P.107, P.108, P.109, P.110, P.111, P.112, P.113, P.114, P.115, P.116, P.117, P.118, P.119, P.120, P.121, P.122, P.123, P.124, P.125, P.126, P.127, P.128, P.129, P.130, P.131, P.132, P.133, P.134, P.135, P.136, P.137, P.138, P.139, P.140, P.141, P.142, P.143, P.144, P.145, P.146, P.147, P.148, P.149, P.150
        
        $camCategory = TrafficSignCategory::where('slug', 'cam')->first();
        $camSigns = [
            ['code' => 'P.101', 'title' => 'Cấm đi ngược chiều', 'description' => 'Cấm các phương tiện đi ngược chiều'],
            ['code' => 'P.102', 'title' => 'Cấm rẽ trái', 'description' => 'Cấm rẽ trái tại vị trí này'],
            ['code' => 'P.103', 'title' => 'Cấm rẽ phải', 'description' => 'Cấm rẽ phải tại vị trí này'],
            ['code' => 'P.104', 'title' => 'Cấm quay đầu', 'description' => 'Cấm các phương tiện quay đầu xe'],
            ['code' => 'P.105', 'title' => 'Cấm vượt', 'description' => 'Cấm các phương tiện vượt nhau'],
            ['code' => 'P.106', 'title' => 'Cấm ô tô vượt', 'description' => 'Cấm ô tô vượt nhau'],
            ['code' => 'P.107', 'title' => 'Cấm ô tô tải vượt', 'description' => 'Cấm ô tô tải vượt nhau'],
            ['code' => 'P.108', 'title' => 'Cấm xe cơ giới', 'description' => 'Cấm tất cả các loại xe cơ giới'],
            ['code' => 'P.109', 'title' => 'Cấm xe ô tô', 'description' => 'Cấm các loại xe ô tô'],
            ['code' => 'P.110', 'title' => 'Cấm xe ô tô và xe máy', 'description' => 'Cấm xe ô tô và xe máy'],
            ['code' => 'P.111', 'title' => 'Cấm xe máy', 'description' => 'Cấm xe máy (mô tô, xe gắn máy)'],
            ['code' => 'P.112', 'title' => 'Cấm xe đạp', 'description' => 'Cấm xe đạp'],
            ['code' => 'P.113', 'title' => 'Cấm xe kéo rơ-moóc', 'description' => 'Cấm xe kéo rơ-moóc'],
            ['code' => 'P.114', 'title' => 'Cấm xe kéo súc vật', 'description' => 'Cấm xe kéo súc vật'],
            ['code' => 'P.115', 'title' => 'Cấm xe thô sơ', 'description' => 'Cấm xe thô sơ (xe đạp, xe xích lô, xe súc vật kéo)'],
            ['code' => 'P.116', 'title' => 'Cấm xe công nông', 'description' => 'Cấm xe công nông'],
            ['code' => 'P.117', 'title' => 'Cấm xe kéo moóc', 'description' => 'Cấm xe kéo moóc'],
            ['code' => 'P.118', 'title' => 'Cấm xe bánh xích', 'description' => 'Cấm xe bánh xích'],
            ['code' => 'P.119', 'title' => 'Cấm người đi bộ', 'description' => 'Cấm người đi bộ'],
            ['code' => 'P.120', 'title' => 'Cấm xe đạp thồ', 'description' => 'Cấm xe đạp thồ'],
            ['code' => 'P.121', 'title' => 'Cấm xe ba bánh loại có động cơ', 'description' => 'Cấm xe ba bánh loại có động cơ'],
            ['code' => 'P.122', 'title' => 'Cấm xe ba bánh loại không có động cơ', 'description' => 'Cấm xe ba bánh loại không có động cơ'],
            ['code' => 'P.123', 'title' => 'Cấm xe kéo rơ-moóc và sơ-mi rơ-moóc', 'description' => 'Cấm xe kéo rơ-moóc và sơ-mi rơ-moóc'],
            ['code' => 'P.124', 'title' => 'Cấm xe ô tô kéo rơ-moóc', 'description' => 'Cấm xe ô tô kéo rơ-moóc'],
            ['code' => 'P.125', 'title' => 'Cấm xe có tải trọng trục vượt quá giá trị ghi trên biển', 'description' => 'Cấm xe có tải trọng trục vượt quá giá trị ghi trên biển'],
            ['code' => 'P.126', 'title' => 'Cấm xe có chiều cao vượt quá giá trị ghi trên biển', 'description' => 'Cấm xe có chiều cao vượt quá giá trị ghi trên biển'],
            ['code' => 'P.127', 'title' => 'Cấm xe có chiều rộng vượt quá giá trị ghi trên biển', 'description' => 'Cấm xe có chiều rộng vượt quá giá trị ghi trên biển'],
            ['code' => 'P.128', 'title' => 'Cấm xe có chiều dài vượt quá giá trị ghi trên biển', 'description' => 'Cấm xe có chiều dài vượt quá giá trị ghi trên biển'],
            ['code' => 'P.129', 'title' => 'Cấm xe có tải trọng toàn bộ vượt quá giá trị ghi trên biển', 'description' => 'Cấm xe có tải trọng toàn bộ vượt quá giá trị ghi trên biển'],
            ['code' => 'P.130', 'title' => 'Cấm dừng xe và đỗ xe', 'description' => 'Cấm dừng xe và đỗ xe'],
            ['code' => 'P.131', 'title' => 'Cấm đỗ xe', 'description' => 'Cấm đỗ xe'],
            ['code' => 'P.132', 'title' => 'Cấm đỗ xe theo giờ', 'description' => 'Cấm đỗ xe theo giờ quy định'],
            ['code' => 'P.133', 'title' => 'Cấm rẽ trái và rẽ phải', 'description' => 'Cấm rẽ trái và rẽ phải'],
            ['code' => 'P.134', 'title' => 'Cấm đi thẳng', 'description' => 'Cấm đi thẳng'],
            ['code' => 'P.135', 'title' => 'Cấm đi thẳng và rẽ trái', 'description' => 'Cấm đi thẳng và rẽ trái'],
            ['code' => 'P.136', 'title' => 'Cấm đi thẳng và rẽ phải', 'description' => 'Cấm đi thẳng và rẽ phải'],
            ['code' => 'P.137', 'title' => 'Cấm rẽ trái và quay đầu', 'description' => 'Cấm rẽ trái và quay đầu'],
            ['code' => 'P.138', 'title' => 'Cấm rẽ phải và quay đầu', 'description' => 'Cấm rẽ phải và quay đầu'],
            ['code' => 'P.139', 'title' => 'Cấm bóp còi', 'description' => 'Cấm bóp còi'],
            ['code' => 'P.140', 'title' => 'Cấm dừng xe, đỗ xe bên trái', 'description' => 'Cấm dừng xe, đỗ xe bên trái'],
            ['code' => 'P.141', 'title' => 'Cấm dừng xe, đỗ xe bên phải', 'description' => 'Cấm dừng xe, đỗ xe bên phải'],
            ['code' => 'P.142', 'title' => 'Cấm dừng xe, đỗ xe hai bên', 'description' => 'Cấm dừng xe, đỗ xe hai bên'],
            ['code' => 'P.143', 'title' => 'Cấm xe có động cơ chạy bằng xăng', 'description' => 'Cấm xe có động cơ chạy bằng xăng'],
            ['code' => 'P.144', 'title' => 'Cấm xe có động cơ chạy bằng dầu', 'description' => 'Cấm xe có động cơ chạy bằng dầu'],
            ['code' => 'P.145', 'title' => 'Cấm xe chở hàng nguy hiểm', 'description' => 'Cấm xe chở hàng nguy hiểm'],
            ['code' => 'P.146', 'title' => 'Cấm xe chở chất nổ', 'description' => 'Cấm xe chở chất nổ'],
            ['code' => 'P.147', 'title' => 'Cấm xe chở chất dễ cháy', 'description' => 'Cấm xe chở chất dễ cháy'],
            ['code' => 'P.148', 'title' => 'Cấm xe chở chất độc', 'description' => 'Cấm xe chở chất độc'],
            ['code' => 'P.149', 'title' => 'Cấm xe chở chất phóng xạ', 'description' => 'Cấm xe chở chất phóng xạ'],
            ['code' => 'P.150', 'title' => 'Cấm xe chở hàng dài', 'description' => 'Cấm xe chở hàng dài'],
        ];

        foreach ($camSigns as $sign) {
            TrafficSign::firstOrCreate(
                ['code' => $sign['code'], 'category_id' => $camCategory->id],
                array_merge($sign, [
                    'category_id' => $camCategory->id,
                    'image_path' => null,
                    'source_url' => '',
                    'source_attrib' => 'Cần thêm hình ảnh: ' . $sign['title']
                ])
            );
        }

        // ========== BIỂN BÁO NGUY HIỂM (W Series) ==========
        // NOTE: Cần thêm hình ảnh cho các biển báo sau:
        // W.201, W.202, W.203, W.204, W.205, W.206, W.207, W.208, W.209, W.210, W.211, W.212, W.213, W.214, W.215, W.216, W.217, W.218, W.219, W.220, W.221, W.222, W.223, W.224, W.225, W.226, W.227, W.228, W.229, W.230, W.231, W.232, W.233, W.234, W.235, W.236, W.237, W.238, W.239, W.240, W.241, W.242, W.243, W.244, W.245, W.246, W.247, W.248, W.249, W.250
        
        $nguyHiemCategory = TrafficSignCategory::where('slug', 'nguy-hiem')->first();
        $nguyHiemSigns = [
            ['code' => 'W.201', 'title' => 'Chỗ ngoặt nguy hiểm vòng bên trái', 'description' => 'Báo hiệu chỗ ngoặt nguy hiểm vòng bên trái'],
            ['code' => 'W.202', 'title' => 'Chỗ ngoặt nguy hiểm vòng bên phải', 'description' => 'Báo hiệu chỗ ngoặt nguy hiểm vòng bên phải'],
            ['code' => 'W.203', 'title' => 'Đường bị hẹp', 'description' => 'Báo hiệu đường bị hẹp'],
            ['code' => 'W.204', 'title' => 'Đường bị hẹp về phía trái', 'description' => 'Báo hiệu đường bị hẹp về phía trái'],
            ['code' => 'W.205', 'title' => 'Đường bị hẹp về phía phải', 'description' => 'Báo hiệu đường bị hẹp về phía phải'],
            ['code' => 'W.206', 'title' => 'Đường hai chiều', 'description' => 'Báo hiệu đường hai chiều'],
            ['code' => 'W.207', 'title' => 'Đường giao nhau', 'description' => 'Báo hiệu đường giao nhau'],
            ['code' => 'W.208', 'title' => 'Giao nhau với đường không ưu tiên', 'description' => 'Báo hiệu giao nhau với đường không ưu tiên'],
            ['code' => 'W.209', 'title' => 'Giao nhau với đường ưu tiên', 'description' => 'Báo hiệu giao nhau với đường ưu tiên'],
            ['code' => 'W.210', 'title' => 'Giao nhau có tín hiệu đèn', 'description' => 'Báo hiệu giao nhau có tín hiệu đèn'],
            ['code' => 'W.211', 'title' => 'Giao nhau với đường sắt không rào chắn', 'description' => 'Báo hiệu giao nhau với đường sắt không rào chắn'],
            ['code' => 'W.212', 'title' => 'Giao nhau với đường sắt có rào chắn', 'description' => 'Báo hiệu giao nhau với đường sắt có rào chắn'],
            ['code' => 'W.213', 'title' => 'Cảnh báo có súc vật', 'description' => 'Báo hiệu cảnh báo có súc vật'],
            ['code' => 'W.214', 'title' => 'Cảnh báo trẻ em', 'description' => 'Báo hiệu cảnh báo trẻ em'],
            ['code' => 'W.215', 'title' => 'Cảnh báo người đi bộ cắt ngang', 'description' => 'Báo hiệu cảnh báo người đi bộ cắt ngang'],
            ['code' => 'W.216', 'title' => 'Cảnh báo xe đạp cắt ngang', 'description' => 'Báo hiệu cảnh báo xe đạp cắt ngang'],
            ['code' => 'W.217', 'title' => 'Cảnh báo đường trơn', 'description' => 'Báo hiệu cảnh báo đường trơn'],
            ['code' => 'W.218', 'title' => 'Cảnh báo lề đường nguy hiểm', 'description' => 'Báo hiệu cảnh báo lề đường nguy hiểm'],
            ['code' => 'W.219', 'title' => 'Cảnh báo đường có sỏi đá', 'description' => 'Báo hiệu cảnh báo đường có sỏi đá'],
            ['code' => 'W.220', 'title' => 'Cảnh báo đường có thể bị sạt lở', 'description' => 'Báo hiệu cảnh báo đường có thể bị sạt lở'],
            ['code' => 'W.221', 'title' => 'Cảnh báo gió ngang', 'description' => 'Báo hiệu cảnh báo gió ngang'],
            ['code' => 'W.222', 'title' => 'Cảnh báo người đi bộ', 'description' => 'Báo hiệu cảnh báo người đi bộ'],
            ['code' => 'W.223', 'title' => 'Cảnh báo cầu vượt liên thông', 'description' => 'Báo hiệu cảnh báo cầu vượt liên thông'],
            ['code' => 'W.224', 'title' => 'Cảnh báo đường cao tốc phía trước', 'description' => 'Báo hiệu cảnh báo đường cao tốc phía trước'],
            ['code' => 'W.225', 'title' => 'Cảnh báo đường hai chiều', 'description' => 'Báo hiệu cảnh báo đường hai chiều'],
            ['code' => 'W.226', 'title' => 'Cảnh báo đường đôi', 'description' => 'Báo hiệu cảnh báo đường đôi'],
            ['code' => 'W.227', 'title' => 'Cảnh báo đường đôi kết thúc', 'description' => 'Báo hiệu cảnh báo đường đôi kết thúc'],
            ['code' => 'W.228', 'title' => 'Cảnh báo đường dốc xuống nguy hiểm', 'description' => 'Báo hiệu cảnh báo đường dốc xuống nguy hiểm'],
            ['code' => 'W.229', 'title' => 'Cảnh báo đường dốc lên nguy hiểm', 'description' => 'Báo hiệu cảnh báo đường dốc lên nguy hiểm'],
            ['code' => 'W.230', 'title' => 'Cảnh báo đường không bằng phẳng', 'description' => 'Báo hiệu cảnh báo đường không bằng phẳng'],
            ['code' => 'W.231', 'title' => 'Cảnh báo đường có gờ giảm tốc', 'description' => 'Báo hiệu cảnh báo đường có gờ giảm tốc'],
            ['code' => 'W.232', 'title' => 'Cảnh báo đường có ổ gà', 'description' => 'Báo hiệu cảnh báo đường có ổ gà'],
            ['code' => 'W.233', 'title' => 'Cảnh báo đường có vật cản', 'description' => 'Báo hiệu cảnh báo đường có vật cản'],
            ['code' => 'W.234', 'title' => 'Cảnh báo đường có công trường', 'description' => 'Báo hiệu cảnh báo đường có công trường'],
            ['code' => 'W.235', 'title' => 'Cảnh báo đường có người làm việc', 'description' => 'Báo hiệu cảnh báo đường có người làm việc'],
            ['code' => 'W.236', 'title' => 'Cảnh báo đường có máy móc thi công', 'description' => 'Báo hiệu cảnh báo đường có máy móc thi công'],
            ['code' => 'W.237', 'title' => 'Cảnh báo đường có xe đẩy', 'description' => 'Báo hiệu cảnh báo đường có xe đẩy'],
            ['code' => 'W.238', 'title' => 'Cảnh báo đường có xe kéo', 'description' => 'Báo hiệu cảnh báo đường có xe kéo'],
            ['code' => 'W.239', 'title' => 'Cảnh báo đường có xe ngựa', 'description' => 'Báo hiệu cảnh báo đường có xe ngựa'],
            ['code' => 'W.240', 'title' => 'Cảnh báo đường có xe trâu bò', 'description' => 'Báo hiệu cảnh báo đường có xe trâu bò'],
            ['code' => 'W.241', 'title' => 'Cảnh báo đường có xe bò', 'description' => 'Báo hiệu cảnh báo đường có xe bò'],
            ['code' => 'W.242', 'title' => 'Cảnh báo đường có xe ngựa kéo', 'description' => 'Báo hiệu cảnh báo đường có xe ngựa kéo'],
            ['code' => 'W.243', 'title' => 'Cảnh báo đường có xe trâu kéo', 'description' => 'Báo hiệu cảnh báo đường có xe trâu kéo'],
            ['code' => 'W.244', 'title' => 'Cảnh báo đường có xe bò kéo', 'description' => 'Báo hiệu cảnh báo đường có xe bò kéo'],
            ['code' => 'W.245', 'title' => 'Cảnh báo đường có xe dê', 'description' => 'Báo hiệu cảnh báo đường có xe dê'],
            ['code' => 'W.246', 'title' => 'Cảnh báo đường có xe lừa', 'description' => 'Báo hiệu cảnh báo đường có xe lừa'],
            ['code' => 'W.247', 'title' => 'Cảnh báo đường có xe la', 'description' => 'Báo hiệu cảnh báo đường có xe la'],
            ['code' => 'W.248', 'title' => 'Cảnh báo đường có xe voi', 'description' => 'Báo hiệu cảnh báo đường có xe voi'],
            ['code' => 'W.249', 'title' => 'Cảnh báo đường có xe thồ', 'description' => 'Báo hiệu cảnh báo đường có xe thồ'],
            ['code' => 'W.250', 'title' => 'Cảnh báo đường có xe xích lô', 'description' => 'Báo hiệu cảnh báo đường có xe xích lô'],
        ];

        foreach ($nguyHiemSigns as $sign) {
            TrafficSign::firstOrCreate(
                ['code' => $sign['code'], 'category_id' => $nguyHiemCategory->id],
                array_merge($sign, [
                    'category_id' => $nguyHiemCategory->id,
                    'image_path' => null,
                    'source_url' => '',
                    'source_attrib' => 'Cần thêm hình ảnh: ' . $sign['title']
                ])
            );
        }

        // ========== BIỂN BÁO HIỆU LỆNH (R Series) ==========
        // NOTE: Cần thêm hình ảnh cho các biển báo sau:
        // R.301, R.302, R.303, R.304, R.305, R.306, R.307, R.308, R.309, R.310, R.311, R.312, R.313, R.314, R.315, R.316, R.317, R.318, R.319, R.320, R.321, R.322, R.323, R.324, R.325, R.326, R.327, R.328, R.329, R.330, R.331, R.332, R.333, R.334, R.335, R.336, R.337, R.338, R.339, R.340, R.341, R.342, R.343, R.344, R.345, R.346, R.347, R.348, R.349, R.350
        
        $hieuLenhCategory = TrafficSignCategory::where('slug', 'hieu-lenh')->first();
        $hieuLenhSigns = [
            ['code' => 'R.301', 'title' => 'Hướng đi phải theo', 'description' => 'Bắt buộc đi theo hướng chỉ dẫn'],
            ['code' => 'R.302', 'title' => 'Hướng phải đi vòng sang phải', 'description' => 'Bắt buộc đi vòng sang phải'],
            ['code' => 'R.303', 'title' => 'Hướng phải đi vòng sang trái', 'description' => 'Bắt buộc đi vòng sang trái'],
            ['code' => 'R.304', 'title' => 'Hướng phải đi thẳng', 'description' => 'Bắt buộc đi thẳng'],
            ['code' => 'R.305', 'title' => 'Hướng phải đi thẳng và rẽ phải', 'description' => 'Bắt buộc đi thẳng và rẽ phải'],
            ['code' => 'R.306', 'title' => 'Hướng phải đi thẳng và rẽ trái', 'description' => 'Bắt buộc đi thẳng và rẽ trái'],
            ['code' => 'R.307', 'title' => 'Hướng phải đi rẽ phải', 'description' => 'Bắt buộc đi rẽ phải'],
            ['code' => 'R.308', 'title' => 'Hướng phải đi rẽ trái', 'description' => 'Bắt buộc đi rẽ trái'],
            ['code' => 'R.309', 'title' => 'Hướng phải đi rẽ phải hoặc rẽ trái', 'description' => 'Bắt buộc đi rẽ phải hoặc rẽ trái'],
            ['code' => 'R.310', 'title' => 'Hướng phải đi vòng sang phải hoặc sang trái', 'description' => 'Bắt buộc đi vòng sang phải hoặc sang trái'],
            ['code' => 'R.311', 'title' => 'Đường dành cho xe cơ giới', 'description' => 'Đường dành cho xe cơ giới'],
            ['code' => 'R.312', 'title' => 'Đường dành cho xe ô tô', 'description' => 'Đường dành cho xe ô tô'],
            ['code' => 'R.313', 'title' => 'Đường dành cho xe máy', 'description' => 'Đường dành cho xe máy'],
            ['code' => 'R.314', 'title' => 'Đường dành cho xe đạp', 'description' => 'Đường dành cho xe đạp'],
            ['code' => 'R.315', 'title' => 'Đường dành cho người đi bộ', 'description' => 'Đường dành cho người đi bộ'],
            ['code' => 'R.316', 'title' => 'Tốc độ tối thiểu cho phép', 'description' => 'Tốc độ tối thiểu cho phép'],
            ['code' => 'R.317', 'title' => 'Tốc độ tối đa cho phép', 'description' => 'Tốc độ tối đa cho phép'],
            ['code' => 'R.318', 'title' => 'Hết hạn chế tốc độ tối đa', 'description' => 'Hết hạn chế tốc độ tối đa'],
            ['code' => 'R.319', 'title' => 'Hết hạn chế tốc độ tối thiểu', 'description' => 'Hết hạn chế tốc độ tối thiểu'],
            ['code' => 'R.320', 'title' => 'Hết tất cả các lệnh cấm', 'description' => 'Hết tất cả các lệnh cấm'],
            ['code' => 'R.321', 'title' => 'Hết hạn chế vượt', 'description' => 'Hết hạn chế vượt'],
            ['code' => 'R.322', 'title' => 'Hết hạn chế còi', 'description' => 'Hết hạn chế còi'],
            ['code' => 'R.323', 'title' => 'Hết hạn chế dừng xe và đỗ xe', 'description' => 'Hết hạn chế dừng xe và đỗ xe'],
            ['code' => 'R.324', 'title' => 'Hết hạn chế đỗ xe', 'description' => 'Hết hạn chế đỗ xe'],
            ['code' => 'R.325', 'title' => 'Hết hạn chế rẽ trái', 'description' => 'Hết hạn chế rẽ trái'],
            ['code' => 'R.326', 'title' => 'Hết hạn chế rẽ phải', 'description' => 'Hết hạn chế rẽ phải'],
            ['code' => 'R.327', 'title' => 'Hết hạn chế quay đầu', 'description' => 'Hết hạn chế quay đầu'],
            ['code' => 'R.328', 'title' => 'Hết hạn chế đi ngược chiều', 'description' => 'Hết hạn chế đi ngược chiều'],
            ['code' => 'R.329', 'title' => 'Hết hạn chế xe cơ giới', 'description' => 'Hết hạn chế xe cơ giới'],
            ['code' => 'R.330', 'title' => 'Hết hạn chế xe ô tô', 'description' => 'Hết hạn chế xe ô tô'],
            ['code' => 'R.331', 'title' => 'Hết hạn chế xe máy', 'description' => 'Hết hạn chế xe máy'],
            ['code' => 'R.332', 'title' => 'Hết hạn chế xe đạp', 'description' => 'Hết hạn chế xe đạp'],
            ['code' => 'R.333', 'title' => 'Hết hạn chế người đi bộ', 'description' => 'Hết hạn chế người đi bộ'],
            ['code' => 'R.334', 'title' => 'Hết hạn chế xe kéo rơ-moóc', 'description' => 'Hết hạn chế xe kéo rơ-moóc'],
            ['code' => 'R.335', 'title' => 'Hết hạn chế xe kéo súc vật', 'description' => 'Hết hạn chế xe kéo súc vật'],
            ['code' => 'R.336', 'title' => 'Hết hạn chế xe thô sơ', 'description' => 'Hết hạn chế xe thô sơ'],
            ['code' => 'R.337', 'title' => 'Hết hạn chế xe công nông', 'description' => 'Hết hạn chế xe công nông'],
            ['code' => 'R.338', 'title' => 'Hết hạn chế xe kéo moóc', 'description' => 'Hết hạn chế xe kéo moóc'],
            ['code' => 'R.339', 'title' => 'Hết hạn chế xe bánh xích', 'description' => 'Hết hạn chế xe bánh xích'],
            ['code' => 'R.340', 'title' => 'Hết hạn chế xe đạp thồ', 'description' => 'Hết hạn chế xe đạp thồ'],
            ['code' => 'R.341', 'title' => 'Hết hạn chế xe ba bánh loại có động cơ', 'description' => 'Hết hạn chế xe ba bánh loại có động cơ'],
            ['code' => 'R.342', 'title' => 'Hết hạn chế xe ba bánh loại không có động cơ', 'description' => 'Hết hạn chế xe ba bánh loại không có động cơ'],
            ['code' => 'R.343', 'title' => 'Hết hạn chế xe kéo rơ-moóc và sơ-mi rơ-moóc', 'description' => 'Hết hạn chế xe kéo rơ-moóc và sơ-mi rơ-moóc'],
            ['code' => 'R.344', 'title' => 'Hết hạn chế xe ô tô kéo rơ-moóc', 'description' => 'Hết hạn chế xe ô tô kéo rơ-moóc'],
            ['code' => 'R.345', 'title' => 'Hết hạn chế tải trọng trục', 'description' => 'Hết hạn chế tải trọng trục'],
            ['code' => 'R.346', 'title' => 'Hết hạn chế chiều cao', 'description' => 'Hết hạn chế chiều cao'],
            ['code' => 'R.347', 'title' => 'Hết hạn chế chiều rộng', 'description' => 'Hết hạn chế chiều rộng'],
            ['code' => 'R.348', 'title' => 'Hết hạn chế chiều dài', 'description' => 'Hết hạn chế chiều dài'],
            ['code' => 'R.349', 'title' => 'Hết hạn chế tải trọng toàn bộ', 'description' => 'Hết hạn chế tải trọng toàn bộ'],
            ['code' => 'R.350', 'title' => 'Hết hạn chế chất nổ', 'description' => 'Hết hạn chế chất nổ'],
        ];

        foreach ($hieuLenhSigns as $sign) {
            TrafficSign::firstOrCreate(
                ['code' => $sign['code'], 'category_id' => $hieuLenhCategory->id],
                array_merge($sign, [
                    'category_id' => $hieuLenhCategory->id,
                    'image_path' => null,
                    'source_url' => '',
                    'source_attrib' => 'Cần thêm hình ảnh: ' . $sign['title']
                ])
            );
        }

        // ========== BIỂN BÁO CHỈ DẪN (I Series) ==========
        // NOTE: Cần thêm hình ảnh cho các biển báo sau:
        // I.401, I.402, I.403, I.404, I.405, I.406, I.407, I.408, I.409, I.410, I.411, I.412, I.413, I.414, I.415, I.416, I.417, I.418, I.419, I.420, I.421, I.422, I.423, I.424, I.425, I.426, I.427, I.428, I.429, I.430, I.431, I.432, I.433, I.434, I.435, I.436, I.437, I.438, I.439, I.440, I.441, I.442, I.443, I.444, I.445, I.446, I.447, I.448, I.449, I.450
        
        $chiDanCategory = TrafficSignCategory::where('slug', 'chi-dan')->first();
        $chiDanSigns = [
            ['code' => 'I.401', 'title' => 'Bắt đầu đường ưu tiên', 'description' => 'Bắt đầu đường ưu tiên'],
            ['code' => 'I.402', 'title' => 'Hết đoạn đường ưu tiên', 'description' => 'Hết đoạn đường ưu tiên'],
            ['code' => 'I.403', 'title' => 'Giao nhau với đường ưu tiên', 'description' => 'Giao nhau với đường ưu tiên'],
            ['code' => 'I.404', 'title' => 'Giao nhau với đường không ưu tiên', 'description' => 'Giao nhau với đường không ưu tiên'],
            ['code' => 'I.405', 'title' => 'Đường một chiều', 'description' => 'Đường một chiều'],
            ['code' => 'I.406', 'title' => 'Đường hai chiều', 'description' => 'Đường hai chiều'],
            ['code' => 'I.407', 'title' => 'Đường đôi', 'description' => 'Đường đôi'],
            ['code' => 'I.408', 'title' => 'Hết đường đôi', 'description' => 'Hết đường đôi'],
            ['code' => 'I.409', 'title' => 'Đường có làn đường dành cho xe khách', 'description' => 'Đường có làn đường dành cho xe khách'],
            ['code' => 'I.410', 'title' => 'Hết làn đường dành cho xe khách', 'description' => 'Hết làn đường dành cho xe khách'],
            ['code' => 'I.411', 'title' => 'Đường có làn đường dành cho xe tải', 'description' => 'Đường có làn đường dành cho xe tải'],
            ['code' => 'I.412', 'title' => 'Hết làn đường dành cho xe tải', 'description' => 'Hết làn đường dành cho xe tải'],
            ['code' => 'I.413', 'title' => 'Đường có làn đường dành cho xe máy', 'description' => 'Đường có làn đường dành cho xe máy'],
            ['code' => 'I.414', 'title' => 'Hết làn đường dành cho xe máy', 'description' => 'Hết làn đường dành cho xe máy'],
            ['code' => 'I.415', 'title' => 'Đường có làn đường dành cho xe đạp', 'description' => 'Đường có làn đường dành cho xe đạp'],
            ['code' => 'I.416', 'title' => 'Hết làn đường dành cho xe đạp', 'description' => 'Hết làn đường dành cho xe đạp'],
            ['code' => 'I.417', 'title' => 'Đường có làn đường dành cho người đi bộ', 'description' => 'Đường có làn đường dành cho người đi bộ'],
            ['code' => 'I.418', 'title' => 'Hết làn đường dành cho người đi bộ', 'description' => 'Hết làn đường dành cho người đi bộ'],
            ['code' => 'I.419', 'title' => 'Đường có làn đường dành cho xe buýt', 'description' => 'Đường có làn đường dành cho xe buýt'],
            ['code' => 'I.420', 'title' => 'Hết làn đường dành cho xe buýt', 'description' => 'Hết làn đường dành cho xe buýt'],
            ['code' => 'I.421', 'title' => 'Đường có làn đường dành cho taxi', 'description' => 'Đường có làn đường dành cho taxi'],
            ['code' => 'I.422', 'title' => 'Hết làn đường dành cho taxi', 'description' => 'Hết làn đường dành cho taxi'],
            ['code' => 'I.423', 'title' => 'Đường có làn đường dành cho xe cứu thương', 'description' => 'Đường có làn đường dành cho xe cứu thương'],
            ['code' => 'I.424', 'title' => 'Hết làn đường dành cho xe cứu thương', 'description' => 'Hết làn đường dành cho xe cứu thương'],
            ['code' => 'I.425', 'title' => 'Đường có làn đường dành cho xe cứu hỏa', 'description' => 'Đường có làn đường dành cho xe cứu hỏa'],
            ['code' => 'I.426', 'title' => 'Hết làn đường dành cho xe cứu hỏa', 'description' => 'Hết làn đường dành cho xe cứu hỏa'],
            ['code' => 'I.427', 'title' => 'Đường có làn đường dành cho xe cảnh sát', 'description' => 'Đường có làn đường dành cho xe cảnh sát'],
            ['code' => 'I.428', 'title' => 'Hết làn đường dành cho xe cảnh sát', 'description' => 'Hết làn đường dành cho xe cảnh sát'],
            ['code' => 'I.429', 'title' => 'Đường có làn đường dành cho xe quân sự', 'description' => 'Đường có làn đường dành cho xe quân sự'],
            ['code' => 'I.430', 'title' => 'Hết làn đường dành cho xe quân sự', 'description' => 'Hết làn đường dành cho xe quân sự'],
            ['code' => 'I.431', 'title' => 'Đường có làn đường dành cho xe ngoại giao', 'description' => 'Đường có làn đường dành cho xe ngoại giao'],
            ['code' => 'I.432', 'title' => 'Hết làn đường dành cho xe ngoại giao', 'description' => 'Hết làn đường dành cho xe ngoại giao'],
            ['code' => 'I.433', 'title' => 'Đường có làn đường dành cho xe VIP', 'description' => 'Đường có làn đường dành cho xe VIP'],
            ['code' => 'I.434', 'title' => 'Hết làn đường dành cho xe VIP', 'description' => 'Hết làn đường dành cho xe VIP'],
            ['code' => 'I.435', 'title' => 'Đường có làn đường dành cho xe tải nặng', 'description' => 'Đường có làn đường dành cho xe tải nặng'],
            ['code' => 'I.436', 'title' => 'Hết làn đường dành cho xe tải nặng', 'description' => 'Hết làn đường dành cho xe tải nặng'],
            ['code' => 'I.437', 'title' => 'Đường có làn đường dành cho xe container', 'description' => 'Đường có làn đường dành cho xe container'],
            ['code' => 'I.438', 'title' => 'Hết làn đường dành cho xe container', 'description' => 'Hết làn đường dành cho xe container'],
            ['code' => 'I.439', 'title' => 'Đường có làn đường dành cho xe rơ-moóc', 'description' => 'Đường có làn đường dành cho xe rơ-moóc'],
            ['code' => 'I.440', 'title' => 'Hết làn đường dành cho xe rơ-moóc', 'description' => 'Hết làn đường dành cho xe rơ-moóc'],
            ['code' => 'I.441', 'title' => 'Đường có làn đường dành cho xe máy kéo', 'description' => 'Đường có làn đường dành cho xe máy kéo'],
            ['code' => 'I.442', 'title' => 'Hết làn đường dành cho xe máy kéo', 'description' => 'Hết làn đường dành cho xe máy kéo'],
            ['code' => 'I.443', 'title' => 'Đường có làn đường dành cho xe nông nghiệp', 'description' => 'Đường có làn đường dành cho xe nông nghiệp'],
            ['code' => 'I.444', 'title' => 'Hết làn đường dành cho xe nông nghiệp', 'description' => 'Hết làn đường dành cho xe nông nghiệp'],
            ['code' => 'I.445', 'title' => 'Đường có làn đường dành cho xe công trình', 'description' => 'Đường có làn đường dành cho xe công trình'],
            ['code' => 'I.446', 'title' => 'Hết làn đường dành cho xe công trình', 'description' => 'Hết làn đường dành cho xe công trình'],
            ['code' => 'I.447', 'title' => 'Đường có làn đường dành cho xe vận tải', 'description' => 'Đường có làn đường dành cho xe vận tải'],
            ['code' => 'I.448', 'title' => 'Hết làn đường dành cho xe vận tải', 'description' => 'Hết làn đường dành cho xe vận tải'],
            ['code' => 'I.449', 'title' => 'Đường có làn đường dành cho xe du lịch', 'description' => 'Đường có làn đường dành cho xe du lịch'],
            ['code' => 'I.450', 'title' => 'Hết làn đường dành cho xe du lịch', 'description' => 'Hết làn đường dành cho xe du lịch'],
        ];

        foreach ($chiDanSigns as $sign) {
            TrafficSign::firstOrCreate(
                ['code' => $sign['code'], 'category_id' => $chiDanCategory->id],
                array_merge($sign, [
                    'category_id' => $chiDanCategory->id,
                    'image_path' => null,
                    'source_url' => '',
                    'source_attrib' => 'Cần thêm hình ảnh: ' . $sign['title']
                ])
            );
        }

        // ========== BIỂN BÁO PHỤ (S Series) ==========
        // NOTE: Cần thêm hình ảnh cho các biển báo sau:
        // S.501, S.502, S.503, S.504, S.505, S.506, S.507, S.508, S.509, S.510, S.511, S.512, S.513, S.514, S.515, S.516, S.517, S.518, S.519, S.520, S.521, S.522, S.523, S.524, S.525, S.526, S.527, S.528, S.529, S.530, S.531, S.532, S.533, S.534, S.535, S.536, S.537, S.538, S.539, S.540, S.541, S.542, S.543, S.544, S.545, S.546, S.547, S.548, S.549, S.550
        
        $phuCategory = TrafficSignCategory::where('slug', 'phu')->first();
        $phuSigns = [
            ['code' => 'S.501', 'title' => 'Phụ cấm đi ngược chiều', 'description' => 'Phụ cấm đi ngược chiều'],
            ['code' => 'S.502', 'title' => 'Phụ cấm rẽ trái', 'description' => 'Phụ cấm rẽ trái'],
            ['code' => 'S.503', 'title' => 'Phụ cấm rẽ phải', 'description' => 'Phụ cấm rẽ phải'],
            ['code' => 'S.504', 'title' => 'Phụ cấm quay đầu', 'description' => 'Phụ cấm quay đầu'],
            ['code' => 'S.505', 'title' => 'Phụ cấm vượt', 'description' => 'Phụ cấm vượt'],
            ['code' => 'S.506', 'title' => 'Phụ cấm ô tô vượt', 'description' => 'Phụ cấm ô tô vượt'],
            ['code' => 'S.507', 'title' => 'Phụ cấm ô tô tải vượt', 'description' => 'Phụ cấm ô tô tải vượt'],
            ['code' => 'S.508', 'title' => 'Phụ cấm xe cơ giới', 'description' => 'Phụ cấm xe cơ giới'],
            ['code' => 'S.509', 'title' => 'Phụ cấm xe ô tô', 'description' => 'Phụ cấm xe ô tô'],
            ['code' => 'S.510', 'title' => 'Phụ cấm xe ô tô và xe máy', 'description' => 'Phụ cấm xe ô tô và xe máy'],
            ['code' => 'S.511', 'title' => 'Phụ cấm xe máy', 'description' => 'Phụ cấm xe máy'],
            ['code' => 'S.512', 'title' => 'Phụ cấm xe đạp', 'description' => 'Phụ cấm xe đạp'],
            ['code' => 'S.513', 'title' => 'Phụ cấm xe kéo rơ-moóc', 'description' => 'Phụ cấm xe kéo rơ-moóc'],
            ['code' => 'S.514', 'title' => 'Phụ cấm xe kéo súc vật', 'description' => 'Phụ cấm xe kéo súc vật'],
            ['code' => 'S.515', 'title' => 'Phụ cấm xe thô sơ', 'description' => 'Phụ cấm xe thô sơ'],
            ['code' => 'S.516', 'title' => 'Phụ cấm xe công nông', 'description' => 'Phụ cấm xe công nông'],
            ['code' => 'S.517', 'title' => 'Phụ cấm xe kéo moóc', 'description' => 'Phụ cấm xe kéo moóc'],
            ['code' => 'S.518', 'title' => 'Phụ cấm xe bánh xích', 'description' => 'Phụ cấm xe bánh xích'],
            ['code' => 'S.519', 'title' => 'Phụ cấm người đi bộ', 'description' => 'Phụ cấm người đi bộ'],
            ['code' => 'S.520', 'title' => 'Phụ cấm xe đạp thồ', 'description' => 'Phụ cấm xe đạp thồ'],
            ['code' => 'S.521', 'title' => 'Phụ cấm xe ba bánh loại có động cơ', 'description' => 'Phụ cấm xe ba bánh loại có động cơ'],
            ['code' => 'S.522', 'title' => 'Phụ cấm xe ba bánh loại không có động cơ', 'description' => 'Phụ cấm xe ba bánh loại không có động cơ'],
            ['code' => 'S.523', 'title' => 'Phụ cấm xe kéo rơ-moóc và sơ-mi rơ-moóc', 'description' => 'Phụ cấm xe kéo rơ-moóc và sơ-mi rơ-moóc'],
            ['code' => 'S.524', 'title' => 'Phụ cấm xe ô tô kéo rơ-moóc', 'description' => 'Phụ cấm xe ô tô kéo rơ-moóc'],
            ['code' => 'S.525', 'title' => 'Phụ cấm tải trọng trục', 'description' => 'Phụ cấm tải trọng trục'],
            ['code' => 'S.526', 'title' => 'Phụ cấm chiều cao', 'description' => 'Phụ cấm chiều cao'],
            ['code' => 'S.527', 'title' => 'Phụ cấm chiều rộng', 'description' => 'Phụ cấm chiều rộng'],
            ['code' => 'S.528', 'title' => 'Phụ cấm chiều dài', 'description' => 'Phụ cấm chiều dài'],
            ['code' => 'S.529', 'title' => 'Phụ cấm tải trọng toàn bộ', 'description' => 'Phụ cấm tải trọng toàn bộ'],
            ['code' => 'S.530', 'title' => 'Phụ cấm chất nổ', 'description' => 'Phụ cấm chất nổ'],
            ['code' => 'S.531', 'title' => 'Phụ cấm chất dễ cháy', 'description' => 'Phụ cấm chất dễ cháy'],
            ['code' => 'S.532', 'title' => 'Phụ cấm chất độc', 'description' => 'Phụ cấm chất độc'],
            ['code' => 'S.533', 'title' => 'Phụ cấm chất phóng xạ', 'description' => 'Phụ cấm chất phóng xạ'],
            ['code' => 'S.534', 'title' => 'Phụ cấm hàng dài', 'description' => 'Phụ cấm hàng dài'],
            ['code' => 'S.535', 'title' => 'Phụ cấm hàng nguy hiểm', 'description' => 'Phụ cấm hàng nguy hiểm'],
            ['code' => 'S.536', 'title' => 'Phụ cấm hàng dễ vỡ', 'description' => 'Phụ cấm hàng dễ vỡ'],
            ['code' => 'S.537', 'title' => 'Phụ cấm hàng dễ cháy', 'description' => 'Phụ cấm hàng dễ cháy'],
            ['code' => 'S.538', 'title' => 'Phụ cấm hàng dễ nổ', 'description' => 'Phụ cấm hàng dễ nổ'],
            ['code' => 'S.539', 'title' => 'Phụ cấm hàng độc hại', 'description' => 'Phụ cấm hàng độc hại'],
            ['code' => 'S.540', 'title' => 'Phụ cấm hàng phóng xạ', 'description' => 'Phụ cấm hàng phóng xạ'],
            ['code' => 'S.541', 'title' => 'Phụ cấm hàng dễ bốc cháy', 'description' => 'Phụ cấm hàng dễ bốc cháy'],
            ['code' => 'S.542', 'title' => 'Phụ cấm hàng dễ bốc hơi', 'description' => 'Phụ cấm hàng dễ bốc hơi'],
            ['code' => 'S.543', 'title' => 'Phụ cấm hàng dễ bốc khói', 'description' => 'Phụ cấm hàng dễ bốc khói'],
            ['code' => 'S.544', 'title' => 'Phụ cấm hàng dễ bốc mùi', 'description' => 'Phụ cấm hàng dễ bốc mùi'],
            ['code' => 'S.545', 'title' => 'Phụ cấm hàng dễ bốc bụi', 'description' => 'Phụ cấm hàng dễ bốc bụi'],
            ['code' => 'S.546', 'title' => 'Phụ cấm hàng dễ bốc tiếng ồn', 'description' => 'Phụ cấm hàng dễ bốc tiếng ồn'],
            ['code' => 'S.547', 'title' => 'Phụ cấm hàng dễ bốc rung', 'description' => 'Phụ cấm hàng dễ bốc rung'],
            ['code' => 'S.548', 'title' => 'Phụ cấm hàng dễ bốc nhiệt', 'description' => 'Phụ cấm hàng dễ bốc nhiệt'],
            ['code' => 'S.549', 'title' => 'Phụ cấm hàng dễ bốc lạnh', 'description' => 'Phụ cấm hàng dễ bốc lạnh'],
            ['code' => 'S.550', 'title' => 'Phụ cấm hàng dễ bốc ẩm', 'description' => 'Phụ cấm hàng dễ bốc ẩm'],
        ];

        foreach ($phuSigns as $sign) {
            TrafficSign::firstOrCreate(
                ['code' => $sign['code'], 'category_id' => $phuCategory->id],
                array_merge($sign, [
                    'category_id' => $phuCategory->id,
                    'image_path' => null,
                    'source_url' => '',
                    'source_attrib' => 'Cần thêm hình ảnh: ' . $sign['title']
                ])
            );
        }
    }
}
