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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('mfa_enabled')->default(false)->after('otp_preference');
            $table->string('mfa_type')->default('email')->after('mfa_enabled')
                ->comment('email, totp');
            $table->text('totp_secret')->nullable()->after('mfa_type')
                ->comment('Encrypted TOTP secret');
            $table->text('backup_codes')->nullable()->after('totp_secret')
                ->comment('Encrypted JSON backup codes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['mfa_enabled', 'mfa_type', 'totp_secret', 'backup_codes']);
        });
    }
};
