<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');                      // اسم الشركة
            $table->string('trade_license')->nullable(); // رقم السجل التجاري
            $table->string('tax_number')->nullable();     // رقم الضريبة
            $table->string('phone_1');                   // رقم الهاتف 1
            $table->string('phone_2')->nullable();       // رقم الهاتف 2
            $table->string('email')->nullable();         // البريد الإلكتروني
            $table->text('address');                     // عنوان الشركة
            $table->string('logo')->nullable();          // مسار شعار الشركة
            $table->string('currency')->default('د.ل');  // العملة الافتراضية
            $table->text('invoice_footer')->nullable();  // تذييل الفاتورة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};