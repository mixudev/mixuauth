<?php

namespace App\Modules\Security\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class LoginLog extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Tabel audit untuk setiap percobaan login, berhasil maupun gagal.
    | Digunakan untuk keperluan investigasi insiden dan pelaporan keamanan.
    |--------------------------------------------------------------------------
    */

    public $timestamps = false; // Menggunakan kolom occurred_at secara manual

    protected $fillable = [
        'user_id',
        'email_attempted',
        'ip_address',
        'device_fingerprint',
        'risk_score',
        'decision',
        'reason_flags',
        'status',
        'occurred_at',
        'country_code',
        'user_agent',
        'ai_response_raw',
    ];

    protected $casts = [
        'reason_flags'    => 'array',
        'ai_response_raw' => 'array',
        'occurred_at'     => 'datetime',
        'risk_score'      => 'integer',
    ];

    // -----------------------------------------------------------------------
    // Konstanta Status Login
    // -----------------------------------------------------------------------

    const STATUS_SUCCESS  = 'success';
    const STATUS_OTP      = 'otp_required';
    const STATUS_BLOCKED  = 'blocked';
    const STATUS_FAILED   = 'failed';        // Password salah
    const STATUS_FALLBACK = 'fallback';      // AI tidak tersedia, gunakan rule-based

    // -----------------------------------------------------------------------
    // Konstanta Keputusan AI
    // -----------------------------------------------------------------------

    const DECISION_ALLOW = 'ALLOW';
    const DECISION_OTP   = 'OTP';
    const DECISION_BLOCK = 'BLOCK';

    // -----------------------------------------------------------------------
    // Model Events
    // -----------------------------------------------------------------------

    protected static function booted()
    {
        static::created(function ($log) {
            // Automatically log to security_notifications for failed/blocked actions
            if (in_array($log->status, [self::STATUS_FAILED, self::STATUS_BLOCKED, self::STATUS_OTP])) {
                $type = 'warning';
                $title = 'Peringatan Keamanan';
                $event = 'auth.' . $log->status;
                
                // Hitung percobaan gagal baru-baru ini untuk konteks pesan yang lebih baik
                $recentFailures = static::where('email_attempted', $log->email_attempted)
                    ->whereIn('status', [self::STATUS_FAILED, self::STATUS_BLOCKED])
                    ->where('occurred_at', '>=', now()->subHours(1))
                    ->count();

                if ($log->status === self::STATUS_BLOCKED) {
                    $type = 'error';
                    $title = 'Akses Dicekal (Block)';
                    $message = "Upaya login ke akun ({$log->email_attempted}) telah diblokir secara otomatis demi keamanan.";
                } elseif ($log->status === self::STATUS_FAILED) {
                    $title = 'Percobaan Login Gagal';
                    $message = "Terdeteksi percobaan login gagal ke akun ({$log->email_attempted}). Total percobaan dalam 1 jam terakhir: {$recentFailures}.";
                    
                    if ($recentFailures >= 3) {
                        $title = 'Percobaan Login Berulang';
                        $type = 'error';
                    }
                } elseif ($log->status === self::STATUS_OTP) {
                    $type = 'info';
                    $title = 'Butuh Verifikasi OTP';
                    $message = "Login ke akun ({$log->email_attempted}) memerlukan verifikasi OTP tambahan.";
                } else {
                    $message = "Aktivitas login mencurigakan terdeteksi pada akun ({$log->email_attempted}).";
                }
                
                \App\Modules\Security\Models\SecurityNotification::create([
                    'user_id'    => $log->user_id, // NULL if email/user not found (becomes Admin Alert)
                    'type'       => $type,
                    'event'      => $event,
                    'title'      => $title,
                    'message'    => $message,
                    'meta'       => [
                        'risk_score'       => $log->risk_score,
                        'recent_failures'  => $recentFailures,
                        'reason_flags'     => $log->reason_flags,
                        'decision'         => $log->decision,
                    ],
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                ]);
            }
        });
    }

    // -----------------------------------------------------------------------
    // Relasi
    // -----------------------------------------------------------------------

    /**
     * Pengguna yang melakukan percobaan login (nullable jika email tidak dikenal).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    /**
     * Filter log berdasarkan keputusan BLOCK untuk monitoring insiden.
     */
    public function scopeBlocked($query)
    {
        return $query->where('decision', self::DECISION_BLOCK);
    }

    /**
     * Filter log dalam rentang waktu tertentu.
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('occurred_at', '>=', now()->subHours($hours));
    }
}
