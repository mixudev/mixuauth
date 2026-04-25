@extends('layouts.auth')

@section('title', 'Akses Tidak Valid')
@section('auth_title', 'Tautan Tidak Valid')
@section('auth_subtitle', $message ?? 'Tautan yang Anda gunakan sudah tidak valid atau sudah kadaluarsa.')
@section('show_ai_banner', true)

@section('auth_content')

    <div style="text-align: center; margin-bottom: 24px;">
        <div style="width: 72px; height: 72px; background: rgba(239, 68, 68, 0.08); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; border: 1px solid rgba(239, 68, 68, 0.15);">
            <svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
        </div>
        
        <p style="font-size: 14px; color: #6B7280; line-height: 1.6; margin-bottom: 32px;">
            Demi keamanan akun Anda, tautan ini dirancang untuk sekali pakai dan memiliki batas waktu tertentu. Silakan masuk secara manual atau minta tautan baru jika diperlukan.
        </p>
    </div>

    <div class="btn-submit-wrap">
        <a href="{{ route('login') }}" class="btn-submit" style="text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i class="fa-solid fa-right-to-bracket" style="font-size: 12px;"></i>
            Masuk ke Akun
        </a>
    </div>

@endsection

@section('auth_footer_extra')
    <a href="{{ route('password.request') }}" style="font-size: 12px; color: #818cf8; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
        Lupa password? Minta link reset
    </a>
@endsection
