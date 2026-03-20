<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan index performa untuk query dashboard yang menangani jutaan records
     * Index ini diperlukan untuk:
     * - Aggregate queries di dashboard
     * - Timeseries queries grouped by date
     * - Top N queries dengan ORDER BY
     */
    public function up(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            // Composite index untuk aggregate queries di date range
            // Digunakan untuk COUNT, SUM operations dengan WHERE occurred_at >= ?
            $table->index(['occurred_at', 'status']);
            
            // Index untuk user_agent distinct queries
            $table->index(['user_agent', 'occurred_at']);
            
            // Index untuk timeseries breakdown by status
            $table->index(['status', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropIndex(['occurred_at', 'status']);
            $table->dropIndex(['user_agent', 'occurred_at']);
            $table->dropIndex(['status', 'occurred_at']);
        });
    }
};
