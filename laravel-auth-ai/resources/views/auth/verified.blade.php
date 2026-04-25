@extends('layouts.auth')

@section('title', 'Email Terverifikasi')
@section('auth_title', 'Email Terverifikasi')
@section('auth_subtitle', 'Selamat! Alamat email Anda telah resmi terkonfirmasi.')
@section('show_ai_banner', true)

@section('auth_content')

    <div style="text-align: center; margin-bottom: 24px;">
        <div style="width: 64px; height: 64px; background: rgba(34, 197, 94, 0.1); border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
            <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#22c55e" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
    </div>

    <div class="btn-submit-wrap">
        @auth
            <a href="{{ route('dashboard') }}" class="btn-submit" style="text-decoration: none; display: block; text-align: center;">
                Masuk ke Dashboard
            </a>
        @else
            <a href="{{ route('login') }}" class="btn-submit" style="text-decoration: none; display: block; text-align: center;">
                Lanjutkan ke Login
            </a>
        @endauth
    </div>

@endsection