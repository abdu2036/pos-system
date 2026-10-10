<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->decimal('balance', 12, 2)->default(0); // موجب: نطالبه بالمال، سالب: يطلبنا مالاً (دين علينا)
            $table->timestamps();
            $table->unique(['name', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};