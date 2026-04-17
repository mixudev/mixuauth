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
        Schema::table('login_logs', function (Blueprint $table) {
            // Kita gunakan raw SQL karena DB::statement('ALTER TABLE...') lebih aman untuk enum di MySQL
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE login_logs MODIFY COLUMN decision ENUM('ALLOW', 'OTP', 'BLOCK', 'PENDING', 'FALLBACK', 'MFA') NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE login_logs MODIFY COLUMN decision ENUM('ALLOW', 'OTP', 'BLOCK', 'PENDING', 'FALLBACK') NULL");
        });
    }
};
