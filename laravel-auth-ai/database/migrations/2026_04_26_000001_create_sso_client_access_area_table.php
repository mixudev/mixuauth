<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel pivot yang menghubungkan SSO Client dengan Access Area yang DIBUTUHKAN.
     * Jika client tidak memiliki baris di tabel ini → semua user aktif boleh akses (open client).
     * Jika client memiliki baris → user HARUS punya SEMUA area yang terdaftar (AND logic).
     */
    public function up(): void
    {
        Schema::create('sso_client_access_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sso_client_id')
                  ->constrained('sso_clients')
                  ->cascadeOnDelete();
            $table->foreignId('access_area_id')
                  ->constrained('access_areas')
                  ->cascadeOnDelete();
            $table->timestamps();

            // Satu client tidak boleh punya duplicate area
            $table->unique(['sso_client_id', 'access_area_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sso_client_access_area');
    }
};
