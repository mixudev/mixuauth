<x-email.base
    subject="Kode Cadangan Keamanan - {{ config('app.name') }}"
    heading="Simpan kode cadangan Anda.">

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:15px;color:#57534e;line-height:1.75;margin:0 0 16px 0;">
        Halo <strong>{{ $name }}</strong>,
    </p>

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:15px;color:#57534e;line-height:1.75;margin:0 0 24px 0;">
        Anda baru saja mengaktifkan Autentikasi Dua Faktor (MFA) menggunakan Google Authenticator. 
        Berikut adalah 10 kode cadangan yang dapat Anda gunakan jika kehilangan akses ke aplikasi authenticator Anda.
    </p>

    {{-- Backup Codes Block --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"
           style="margin-bottom:24px;">
        <tr>
            <td style="background-color:#fafaf9;border:1px solid #e7e5e4;border-radius:2px;
                       border-top:3px solid #1c1917;padding:24px;text-align:center;">
                <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
                          font-size:11px;font-weight:600;color:#a8a29e;
                          text-transform:uppercase;letter-spacing:0.1em;
                          margin:0 0 12px 0;">
                    Kode Cadangan Anda
                </p>
                
                <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                    @foreach(array_chunk($backupCodes, 2) as $chunk)
                    <tr>
                        @foreach($chunk as $code)
                        <td align="center" style="padding: 5px;">
                            <code style="font-family:'Courier New',Courier,monospace; font-size:18px; font-weight:700; color:#1c1917; letter-spacing:0.05em; background:#f3f4f6; padding:8px 12px; border-radius:4px; display:block;">
                                {{ $code }}
                            </code>
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:13px;color:#dc2626;line-height:1.6;margin:0 0 24px 0; font-weight: 600;">
        PENTING: Simpan kode ini di tempat yang sangat aman. Setiap kode hanya dapat digunakan satu kali.
    </p>

    <x-email.divider />

    <p style="font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;
              font-size:13px;color:#a8a29e;line-height:1.6;margin:0;">
        Jika Anda tidak merasa melakukan aktivasi ini, harap hubungi tim dukungan kami segera.
    </p>

</x-email.base>
