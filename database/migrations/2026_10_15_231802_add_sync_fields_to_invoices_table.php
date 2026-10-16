<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique();
            }
            if (!Schema::hasColumn('invoices', 'is_synced')) {
                $table->boolean('is_synced')->default(true);
            }
            if (!Schema::hasColumn('invoices', 'synced_at')) {
                $table->timestamp('synced_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'is_synced', 'synced_at']);
        });
    }
};