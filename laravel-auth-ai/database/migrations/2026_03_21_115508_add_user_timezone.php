<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom timezone ke tabel users.
     *
     * Jalankan: php artisan migrate
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('timezone', 50)
                  ->default('UTC')
                  ->after('email')
                  ->comment('Timezone IANA user, contoh: Asia/Jakarta, Asia/Makassar, Asia/Jayapura');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};