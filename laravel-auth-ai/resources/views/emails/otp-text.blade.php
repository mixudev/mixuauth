<x-email.base-text heading="Kode Verifikasi Login">

Halo {{ $userName }},

Kami mendeteksi percobaan login ke akun Anda yang memerlukan verifikasi tambahan.

KODE VERIFIKASI ANDA:
{{ $otpCode }}

Kode ini berlaku selama {{ $expiresMinutes }} menit dan hanya dapat digunakan satu kali.

@if($ipAddress ?? false)
Percobaan login dari IP: {{ $ipAddress }}
@endif

Jika Anda tidak sedang melakukan login, abaikan email ini dan pertimbangkan untuk mengganti password Anda.

</x-email.base-text>
