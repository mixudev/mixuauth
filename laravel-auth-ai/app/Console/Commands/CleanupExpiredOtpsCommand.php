<?php

namespace App\Console\Commands;

use App\Modules\Authentication\Models\OtpVerification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredOtpsCommand extends Command
{
    /*
    |--------------------------------------------------------------------------
    | Command untuk membersihkan rekaman OTP yang sudah kedaluwarsa.
    |
    | Dijadwalkan berjalan setiap jam via Laravel Scheduler.
    | Menjaga ukuran tabel otp_verifications tetap terkontrol.
    |--------------------------------------------------------------------------
    */

    protected $signature   = 'auth:cleanup-otps {--dry-run : Tampilkan jumlah yang akan dihapus tanpa benar-benar menghapus}';
    protected $description  = 'Hapus rekaman OTP yang sudah kedaluwarsa atau sudah diverifikasi lebih dari 24 jam yang lalu.';

    public function handle(): int
    {
        // Hapus OTP yang kedaluwarsa DAN OTP yang sudah terverifikasi lebih dari 24 jam
        $query = OtpVerification::query()->where(function ($q) {
            $q->where('expires_at', '<', now())
              ->orWhere(function ($inner) {
                  $inner->whereNotNull('verified_at')
                        ->where('verified_at', '<', now()->subHours(24));
              });
        });

        $count = $query->count();

        if ($this->option('dry-run')) {
            $this->info("Dry run: {$count} rekaman OTP akan dihapus.");
            return Command::SUCCESS;
        }

        $deleted = $query->delete();

        Log::channel('security')->info("Pembersihan OTP selesai: {$deleted} rekaman dihapus.");
        $this->info("Berhasil menghapus {$deleted} rekaman OTP kedaluwarsa.");

        return Command::SUCCESS;
    }
}
