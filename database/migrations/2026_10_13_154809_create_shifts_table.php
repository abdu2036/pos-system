<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // الكاشير
            $table->foreignId('treasury_id')->constrained()->cascadeOnDelete(); // الخزينة المرتبطة
            $table->decimal('opening_balance', 10, 2)->default(0); // عهدة بداية الوردية
            $table->decimal('total_sales', 10, 2)->default(0); // إجمالي المبيعات
            $table->decimal('total_cash_sales', 10, 2)->default(0); // المبيعات النقدية
            $table->decimal('closing_balance', 10, 2)->nullable(); // المبلغ الفعلي بالدرج عند الإغلاق
            $table->decimal('expected_balance', 10, 2)->nullable(); // المبلغ المتوقع
            $table->decimal('difference', 10, 2)->default(0); // العجز أو الزيادة
            $table->enum('status', ['open', 'closed'])->default('open'); // حالة الوردية
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};