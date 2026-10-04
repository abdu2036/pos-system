<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();

            // ربط الفرع بالشركة
            $table->foreignId('company_id')
                  ->constrained('company_settings')
                  ->cascadeOnDelete();

            $table->string('name');                      // اسم الفرع
            $table->string('code')->unique();            // كود الفرع
            $table->string('phone')->nullable();         // هاتف الفرع
            $table->text('address')->nullable();          // عنوان الفرع
            $table->boolean('is_main')->default(false);  // هل هو الفرع الرئيسي للمؤسسة؟
            $table->boolean('is_active')->default(true);  // حالة الفرع (نشط / متوقف)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};