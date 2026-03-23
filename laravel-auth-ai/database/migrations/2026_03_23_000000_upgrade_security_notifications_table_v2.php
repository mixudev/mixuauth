<?php
/**
 * Upgrade Security Notifications Table
 * Version: 2.0 (Production Ready)
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old table to ensure clean state with new professional schema
        Schema::dropIfExists('security_notifications');

        Schema::create('security_notifications', function (Blueprint $table) {
            $table->id();

            // Target user (null = global / system)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Kategori / severity: error, warning, info, success
            $table->string('type', 30)->index(); 

            // Sumber event: auth.failed, ip.blocked, account.password_changed, etc.
            $table->string('event', 50)->index(); 

            // Konten
            $table->string('title', 150);
            $table->text('message');

            // Metadata tambahan (fleksibel untuk menyimpan detail unik per event)
            $table->json('meta')->nullable();

            // Context keamanan
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            // State: NULL = Unread, Timestamp = Read Time
            $table->timestamp('read_at')->nullable()->index();

            $table->timestamps();

            // Composite index untuk performa query list per user & status
            $table->index(['user_id', 'read_at', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_notifications');
    }
};
