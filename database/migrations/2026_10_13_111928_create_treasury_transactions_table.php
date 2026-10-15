<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treasury_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treasury_id')->constrained('treasuries')->onDelete('cascade'); // الخزينة المرتبطة
            $table->string('type'); // نوع الحركة: deposit (إيداع/قبض)، withdrawal (سحب/صرف)
            $table->decimal('amount', 15, 2); // المبلغ
            $table->string('source_type')->nullable(); // مصدر الحركة (sale: مبيعات, purchase: مششتريات, customer_payment: سند زبون, supplier_payment: سند مورد, expense: مصروفات, manual: يدوي)
            $table->unsignedBigInteger('source_id')->nullable(); // معرف الفاتورة أو السند المرتبط
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // المستخدم الذي قام بالحركة
            $table->text('notes')->nullable(); // بيان تفصيلي للحركة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treasury_transactions');
    }
};