<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('category_id')->constrained()->onDelete('cascade');
        $table->string('name');
        $table->string('sku')->unique()->nullable(); // رمز المنتج
        $table->string('barcode')->unique()->nullable(); // الباركود
        $table->decimal('cost_price', 10, 2)->default(0); // سعر الشراء
        $table->decimal('selling_price', 10, 2); // سعر البيع
        $table->string('unit')->default('piece'); // الوحدة (حبة، كيلو، طرد)
        $table->boolean('status')->default(true);
        $table->timestamps();
    });

    // جدول كميات المنتجات حسب كل فرع
    Schema::create('product_branch', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_id')->constrained()->onDelete('cascade');
        $table->foreignId('branch_id')->constrained()->onDelete('cascade');
        $table->integer('quantity')->default(0); // الكمية المتوفرة بالفرع
        $table->integer('alert_quantity')->default(5); // حد التنبيه بنقص المخزون
        $table->timestamps();

        $table->unique(['product_id', 'branch_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
