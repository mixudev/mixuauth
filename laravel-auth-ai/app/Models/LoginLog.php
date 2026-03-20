<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
                if ($log->status === self::STATUS_BLOCKED) {
                    $type = 'error';
                }
                
                \App\Models\SecurityNotification::create([
                    'type' => $type,
                    'title' => 'Peringatan Keamanan. Status: ' . strtoupper($log->status),
                    'message' => 'Upaya login ke akun (' . ($log->email_attempted ?? 'Unknown') . ') dari IP ' . $log->ip_address,
                    'ip_address' => $log->ip_address,
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
