@extends('layouts.auth')

@section('title', 'Lupa Password')
@section('auth_title', 'Lupa Password?')
@section('auth_subtitle', 'Masukkan email Anda dan kami akan mengirimkan link untuk mereset password.')
@section('show_ai_banner', true)

@section('auth_content')

    {{-- FORM --}}
    <form action="{{ route('password.email') }}" method="POST">
        @csrf

        {{-- Email field --}}
        <div class="form-group">
            <label for="email" class="form-label">Alamat Email</label>
            <input
                id="email"
                name="email"
                type="email"
                autocomplete="email"
                required
                value="{{ old('email') }}"
                placeholder="nama@email.com"
                class="form-input @error('email') is-error @enderror"
            >
            @error('email')
                <div class="field-error">{{ $message }}</div>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="btn-submit-wrap">
            <button type="submit" class="btn-submit">
                Kirim Link Reset
            </button>
        </div>

    </form>

@endsection

@section('auth_footer_extra')
    <a href="{{ route('login') }}" style="font-size: 12px; color: #9CA3AF; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali ke halaman login
    </a>
@endsection