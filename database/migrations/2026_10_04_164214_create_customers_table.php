<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->decimal('balance', 12, 2)->default(0); // موجب: عليه دين (يجب أن يدفع)، سالب: له رصيد
            $table->decimal('credit_limit', 12, 2)->default(0); // الحد الأقصى للدين
            $table->timestamps();
            $table->unique(['name', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};