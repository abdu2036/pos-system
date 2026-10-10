<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->morphs('accountable'); // يربط المعاملة بالزبون أو المورد
            $table->enum('type', ['payment', 'invoice', 'return']); // نوع الحركة: سداد، فاتورة، ترجيع
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('cash'); // cash, card, transfer
            $table->string('notes')->nullable();
            $table->timestamps();
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};