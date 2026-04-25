<x-email.base
    subject="Login Google Aktif - {{ config('app.name') }}"
    heading="Aktivasi Login Google Berhasil.">

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:15px;color:#57534e;line-height:1.75;margin:0 0 16px 0;">
        Halo <strong>{{ $name }}</strong>,
    </p>

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:15px;color:#57534e;line-height:1.75;margin:0 0 24px 0;">
        Kami ingin memberitahu Anda bahwa akun Anda ({{ $email }}) kini telah berhasil dikaitkan dengan akun Google Anda. 
        Mulai sekarang, Anda dapat masuk ke aplikasi kami dengan jauh lebih mudah menggunakan tombol <strong>"Login with Google"</strong>.
    </p>

    {{-- Security notice --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"
           style="background-color:#f0f9ff;border:1px solid #bae6fd;border-radius:2px;
                  margin-bottom:24px;">
        <tr>
            <td style="padding:16px 20px;">
                <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                          font-size:11px;font-weight:600;color:#0369a1;
                          text-transform:uppercase;letter-spacing:0.08em;
                          margin:0 0 8px 0;">
                    Informasi Penting
                </p>
                <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                          font-size:13px;color:#0369a1;line-height:1.6;margin:0;">
                    Password lama Anda tetap berlaku dan tidak berubah. Anda masih bisa login menggunakan email dan password seperti biasa jika diinginkan.
                </p>
            </td>
        </tr>
    </table>

    {{-- Action Button --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin-bottom:30px;">
        <tr>
            <td align="center">
                <a href="{{ $loginUrl }}" style="background-color:#1c1917; color:#ffffff; padding:12px 24px; text-decoration:none; border-radius:4px; font-weight:bold; display:inline-block; font-size:14px;">
                    Pergi ke Halaman Login
                </a>
            </td>
        </tr>
    </table>

    <x-email.divider />

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:13px;color:#a8a29e;line-height:1.6;margin:0;">
        Jika Anda merasa tidak melakukan pengaitan akun ini, silakan hubungi tim keamanan kami segera untuk melindungi akun Anda.
    </p>

</x-email.base>
