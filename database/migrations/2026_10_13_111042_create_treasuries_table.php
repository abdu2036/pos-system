<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treasuries', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الخزينة أو الحساب (مثل: الخزينة الرئيسية، صندوق الكاشير، حساب البنك)
            $table->string('type')->default('cash'); // نوع الحساب: cash (نقدى/صندوق), bank (بنكي)
            $table->decimal('opening_balance', 15, 2)->default(0.00); // رصيد أول المدة
            $table->decimal('current_balance', 15, 2)->default(0.00); // الرصيد الحالي (يتحدث تلقائياً مع الحركات)
            $table->boolean('is_active')->default(true); // حالة الخزينة (نشطة / مغلقة)
            $table->text('notes')->nullable(); // ملاحظات
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treasuries');
    }
};