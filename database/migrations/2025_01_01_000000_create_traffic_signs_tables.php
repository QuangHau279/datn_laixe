// database/migrations/2025_01_01_000000_create_traffic_signs_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('traffic_sign_categories', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();         // cam, nguy-hiem, hieu-lenh, chi-dan, phu
            $t->string('name');                   // Cấm, Nguy hiểm, Hiệu lệnh, Chỉ dẫn, Phụ
            $t->timestamps();
        });

        Schema::create('traffic_signs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained('traffic_sign_categories')->cascadeOnDelete();
            $t->string('code')->nullable();       // VD: P.101, W.201a...
            $t->string('title');                  // Tên biển
            $t->text('description')->nullable();  // Mô tả
            $t->string('image_path');             // storage url
            $t->string('source_url');             // nguồn gốc ảnh
            $t->string('source_attrib')->nullable();
            $t->timestamps();
            $t->index(['category_id', 'code']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('traffic_signs');
        Schema::dropIfExists('traffic_sign_categories');
    }
};
