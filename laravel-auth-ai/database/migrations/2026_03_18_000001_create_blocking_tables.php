<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel blacklist IP
        Schema::create('ip_blacklist', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique()->index();
            $table->string('reason', 255)->nullable();
            $table->string('blocked_by', 50)->default('auto'); // 'auto' | 'manual'
            $table->unsignedInteger('block_count')->default(1); // berapa kali auto-block
            $table->timestamp('blocked_until')->nullable()->index(); // null = permanen
            $table->timestamp('blocked_at');
            $table->timestamps();
        });

        // Tabel whitelist IP (selalu ALLOW, skip AI check)
        Schema::create('ip_whitelist', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->unique()->index();
            $table->string('label', 255)->nullable(); // deskripsi, misal: "Office Jakarta"
            $table->string('added_by', 100)->nullable();
            $table->timestamps();
        });

        // Tabel blocked users (auto-lock setelah N kali BLOCK)
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 255)->nullable();
            $table->string('blocked_by', 50)->default('auto');
            $table->unsignedTinyInteger('block_count')->default(1);
            $table->timestamp('blocked_until')->nullable(); // null = permanen
            $table->timestamp('unblocked_at')->nullable();
            $table->string('unblocked_by', 100)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'blocked_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_blocks');
        Schema::dropIfExists('ip_whitelist');
        Schema::dropIfExists('ip_blacklist');
    }
};
