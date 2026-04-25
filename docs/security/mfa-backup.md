# Multi-Factor Authentication (MFA) & Backup Codes

Multi-Factor Authentication merupakan benteng pertahanan terakhir apabila kata sandi pengguna telah terkompromi (password breach) dan sistem AI mendeteksi adanya risiko menengah (Medium Risk).

## Arsitektur MFA

Pada sistem AI Auth, kami menyediakan berbagai channel (saluran) untuk pengiriman kode *One Time Password (OTP)*.

1. **Email OTP**: Pengiriman standar melalui SMTP.
2. **WhatsApp OTP**: Diintegrasikan menggunakan *Fonte Gateway* untuk penyampaian pesan instan.(segera hadir)
3. **Authenticator App**: Algoritma _Time-Based One-Time Password (TOTP)_.
4. **Backup Codes**: Kode statis 8 digit yang digenerate satu kali dan digunakan ketika perangkat/saluran utama tidak dapat diakses.

## Flow Validasi MFA

Ketika `AuthFlowService` memutuskan pengguna memerlukan MFA, sistem tidak langsung meloginkan pengguna, melainkan menyimpan "State" sementara menggunakan Cache atau Session.

**Kode Implementasi:**
```php {6,15}
// app/Modules/Authentication/Controllers/MfaController.php

public function verify(Request $request) {
    $request->validate(['otp' => 'required|string|size:6']);
    
    // 1. Ambil state sementara dari session
    $tempUserId = session('mfa_temp_user_id');
    $user = clone User::find($tempUserId);

    if (!$user || !$this->mfaService->verifyOtp($user, $request->otp)) {
        return back()->withErrors(['otp' => 'Kode OTP tidak valid atau kadaluarsa.']);
    }

    // 2. Jika valid, login pengguna seutuhnya
    Auth::login($user);
    session()->forget(['mfa_temp_user_id', 'mfa_required']);
    
    return redirect()->intended('/dashboard');
}
```

::: warning Penguncian OTP (Throttle)
Untuk menghindari serangan penebakan OTP secara *brute-force*, endpoint `/auth/mfa/verify` dilindungi oleh *Rate Limiter* ketat. Standarnya adalah maksimal **3 kali kegagalan** dalam kurun waktu 5 menit. Jika melebihi batas, IP dan Akun pengguna akan dikunci sementara.
:::

## Mekanisme Backup Codes

_Backup Codes_ adalah daftar kode acak yang disediakan kepada pengguna saat mereka pertama kali mengaktifkan MFA. Kode ini bersifat sekali pakai (One-Time Use).

**Penyimpanan Backup Codes:**
Sistem tidak menyimpan kode ini dalam _plain text_. Kode di-_hash_ layaknya _password_ konvensional di database menggunakan algoritma _Bcrypt_.

**Pembuatan & Pengiriman Email:**
```php {8}
// app/Modules/Authentication/Services/MfaService.php

public function generateBackupCodes(User $user): array {
    $codes = [];
    $hashedCodes = [];

    for ($i = 0; $i < 8; $i++) {
        $plainCode = Str::random(8); // Kode 8 digit
        $codes[] = $plainCode;
        $hashedCodes[] = Hash::make($plainCode);
    }

    // Simpan hash ke database
    $user->backupCodes()->createMany(
        array_map(fn($hash) => ['code_hash' => $hash], $hashedCodes)
    );

    // Kirim versi plain text ke email pengguna
    Mail::to($user)->queue(new BackupCodesMail($codes));
    
    return $codes;
}
```

::: tip Keamanan Penyampaian
Pastikan worker antrean surel (`php artisan queue:work`) berjalan lancar di lingkungan _production_ agar pengguna segera menerima Backup Codes mereka tanpa _delay_.
:::
