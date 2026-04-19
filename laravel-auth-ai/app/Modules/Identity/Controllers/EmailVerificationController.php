<?php

namespace App\Modules\Identity\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use App\Modules\Identity\Notifications\VerifyEmailNotification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EmailVerificationController extends Controller
{
    /**
     * Kirim email verifikasi ke user yang login.
     */
    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'status'  => 'info',
                'message' => 'Email Anda sudah terverifikasi.'
            ]);
        }

        $token = Str::random(60);
        $uuid = (string) Str::uuid();

        DB::table('email_verification_tokens')
            ->where('email', $request->user()->email)
            ->delete();

        DB::table('email_verification_tokens')->insert([
            'id' => $uuid,
            'email' => $request->user()->email,
            'token' => Hash::driver('argon2id')->make($token),
            'expires_at' => Carbon::now()->addMinutes(60),
            'created_at' => Carbon::now()
        ]);

        // Kirim notifikasi verifikasi custom
        $request->user()->notify(new VerifyEmailNotification($uuid, $token));

        return response()->json([
            'status'  => 'success',
            'message' => 'Link verifikasi telah dikirim ke email Anda.'
        ]);
    }

    /**
     * Kirim email verifikasi ke user tertentu (khusus admin).
     */
    public function sendToUser(Request $request, $id)
    {
        abort_unless($request->user()?->can('access-admin-security'), 403);
        $user = User::findOrFail($id);
        
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status'  => 'info',
                'message' => 'Email pengguna sudah terverifikasi.'
            ]);
        }

        $token = Str::random(60);
        $uuid = (string) Str::uuid();

        DB::table('email_verification_tokens')
            ->where('email', $user->email)
            ->delete();

        DB::table('email_verification_tokens')->insert([
            'id' => $uuid,
            'email' => $user->email,
            'token' => Hash::driver('argon2id')->make($token),
            'expires_at' => Carbon::now()->addMinutes(60),
            'created_at' => Carbon::now()
        ]);

        // Kirim notifikasi verifikasi custom
        $user->notify(new VerifyEmailNotification($uuid, $token));

        return response()->json([
            'status'  => 'success',
            'message' => 'Link verifikasi telah dikirim ke email pengguna.'
        ]);
    }

    /**
     * Proses verifikasi dari link di email.
     */
public function verify(Request $request, $uuid, $token)
{
    $record = DB::table('email_verification_tokens')->where('id', $uuid)->first();

    if (!$record || Carbon::parse($record->expires_at)->isPast()) {
        abort(403, 'Link verifikasi tidak valid atau sudah kedaluwarsa.');
    }

    if (!Hash::driver('argon2id')->check($token, $record->token)) {
        abort(403, 'Link verifikasi tidak valid.');
    }

    $user = User::where('email', $record->email)->firstOrFail();

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new \Illuminate\Auth\Events\Verified($user));
    }

    // Hapus token (single-use)
    DB::table('email_verification_tokens')->where('id', $uuid)->delete();

    // Buat signed URL untuk halaman verified, valid 5 menit
    $signedUrl = URL::signedRoute('verification.verified', [], now()->addMinutes(5));

    return redirect($signedUrl);
}

    /**
     * Halaman sukses verifikasi.
     */
    public function verified()
    {
        return view('auth.verified');
    }
}
