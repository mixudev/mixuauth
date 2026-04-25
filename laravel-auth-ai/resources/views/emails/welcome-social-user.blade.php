<x-email.base
    subject="Selamat Datang di {{ config('app.name') }}"
    heading="Akun Anda telah siap.">

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:15px;color:#57534e;line-height:1.75;margin:0 0 16px 0;">
        Halo <strong>{{ $name }}</strong>,
    </p>

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:15px;color:#57534e;line-height:1.75;margin:0 0 24px 0;">
        Selamat datang! Akun Anda telah berhasil dibuat secara otomatis melalui Google Login. 
        Anda dapat menggunakan kredensial di bawah ini untuk masuk secara manual di masa mendatang.
    </p>

    {{-- Credentials Block --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"
           style="margin-bottom:24px;">
        <tr>
            <td style="background-color:#fafaf9;border:1px solid #e7e5e4;border-radius:2px;
                       border-top:3px solid #6366f1;padding:20px;text-align:left;">
                <p style="margin:0 0 10px 0; font-size:12px; color:#a8a29e; text-transform:uppercase; letter-spacing:0.05em;">Detail Login Manual</p>
                <p style="margin:0 0 5px 0; font-size:14px; color:#57534e;"><strong>Email:</strong> {{ $email }}</p>
                <p style="margin:0; font-size:14px; color:#57534e;"><strong>Password:</strong> <code style="background:#eee; padding:2px 4px;">{{ $password }}</code></p>
            </td>
        </tr>
    </table>

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:14px;color:#dc2626;line-height:1.6;margin:0 0 24px 0; font-style: italic;">
        PENTING: Segera ganti password Anda demi keamanan. Link di bawah hanya berlaku hingga jam <strong>{{ $expiresAt }}</strong>.
    </p>

    {{-- Action Buttons --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin-bottom:30px;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation">
                    <tr>
                        <td align="center" style="padding-right: 10px;">
                            <a href="{{ $resetUrl }}" style="background-color:#ef4444; color:#ffffff; padding:12px 20px; text-decoration:none; border-radius:4px; font-weight:bold; display:inline-block; font-size:14px;">
                                Reset Password Sekarang
                            </a>
                        </td>
                        <td align="center">
                            <a href="{{ $magicLoginUrl }}" style="background-color:#6366f1; color:#ffffff; padding:12px 20px; text-decoration:none; border-radius:4px; font-weight:bold; display:inline-block; font-size:14px;">
                                Masuk ke Dashboard
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <x-email.divider />

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:13px;color:#a8a29e;line-height:1.6;margin:0;">
        Jika Anda mengalami masalah saat masuk, silakan hubungi tim dukungan kami atau gunakan fitur Lupa Password di halaman login.
    </p>

</x-email.base>
