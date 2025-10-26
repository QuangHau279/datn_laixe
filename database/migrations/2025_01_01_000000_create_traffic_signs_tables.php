<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('traffic_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('traffic_sign_categories')->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('source_url');
            $table->string('source_attrib')->nullable();
            $table->timestamps();
            $table->index(['category_id', 'code']);
        });
    }
    
    public function down(): void {
        Schema::dropIfExists('traffic_signs');
    }
};
