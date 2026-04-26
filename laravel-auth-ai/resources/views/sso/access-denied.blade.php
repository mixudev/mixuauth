@extends('layouts.auth')

@section('title', 'Akses Ditolak')
@section('sidebar_headline', 'Akses Ditolak.')
@section('sidebar_sub', 'Anda tidak memiliki izin untuk mengakses aplikasi ini.')

@section('auth_title', 'Akses Tidak Diizinkan')
@section('auth_subtitle', 'Akun Anda tidak memenuhi syarat akses untuk aplikasi ini.')

@section('auth_content')
<div style="text-align: center; margin-bottom: 24px;">

    {{-- Icon --}}
    <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 50%; background: rgba(239,68,68,0.1); margin-bottom: 16px;">
        <svg style="width:32px; height:32px; color:#ef4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
        </svg>
    </div>

    @php $appName = session('app_name', 'Aplikasi ini'); @endphp
    @php $reason  = session('denied_reason', 'access_area'); @endphp

    <p style="font-size: 14px; color: var(--auth-text); margin-bottom: 4px;">
        Anda mencoba mengakses
    </p>
    <p style="font-size: 16px; font-weight: 700; color: var(--auth-heading, #1e293b); margin-bottom: 20px;">
        {{ $appName }}
    </p>

    @if($reason === 'client_inactive')
        {{-- Client nonaktif --}}
        <div style="background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.2); border-radius: 10px; padding: 14px 16px; text-align: left; margin-bottom: 20px;">
            <p style="font-size: 13px; color: #b91c1c; font-weight: 600; margin-bottom: 4px;">
                <i class="fa-solid fa-power-off" style="margin-right: 6px;"></i>Aplikasi Sedang Nonaktif
            </p>
            <p style="font-size: 12px; color: var(--auth-text); margin: 0;">
                Aplikasi <strong>{{ $appName }}</strong> saat ini telah dinonaktifkan oleh administrator sistem. Hubungi administrator untuk informasi lebih lanjut.
            </p>
        </div>

    @else
        {{-- Access area mismatch --}}
        @php
            $requiredAreas = session('required_areas', collect());
            $userAreas     = session('user_areas', []);
            $missingAreas  = session('missing_areas', []);
        @endphp

        <div style="background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.2); border-radius: 10px; padding: 14px 16px; text-align: left; margin-bottom: 16px;">
            <p style="font-size: 13px; color: #b91c1c; font-weight: 600; margin-bottom: 10px;">
                <i class="fa-solid fa-shield-halved" style="margin-right: 6px;"></i>Area Akses Tidak Memenuhi Syarat
            </p>
            <p style="font-size: 12px; color: var(--auth-text); margin-bottom: 10px;">
                Aplikasi <strong>{{ $appName }}</strong> membutuhkan izin akses yang belum dimiliki akun Anda.
            </p>

            @if($requiredAreas->count() > 0)
            <div style="margin-bottom: 8px;">
                <p style="font-size: 11px; font-weight: 700; color: var(--auth-text); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
                    Dibutuhkan:
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                    @foreach($requiredAreas as $area)
                        @php $isMissing = in_array($area->slug, $missingAreas); @endphp
                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 600;
                            background: {{ $isMissing ? 'rgba(239,68,68,0.12)' : 'rgba(16,185,129,0.12)' }};
                            color: {{ $isMissing ? '#b91c1c' : '#065f46' }};
                            border: 1px solid {{ $isMissing ? 'rgba(239,68,68,0.25)' : 'rgba(16,185,129,0.25)' }};">
                            <i class="fa-solid {{ $isMissing ? 'fa-xmark' : 'fa-check' }}" style="font-size: 9px;"></i>
                            {{ $area->name }}
                        </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        @if(!empty($missingAreas))
        <div style="background: rgba(245,158,11,0.06); border: 1px solid rgba(245,158,11,0.2); border-radius: 8px; padding: 10px 14px; text-align: left; margin-bottom: 16px;">
            <p style="font-size: 12px; color: #92400e; margin: 0;">
                <i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i>
                Hubungi administrator sistem untuk mendapatkan akses ke area yang diperlukan.
            </p>
        </div>
        @endif
    @endif

    {{-- Tombol kembali --}}
    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 8px;">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : '/' }}"
           class="btn-primary"
           style="display: block; text-align: center; text-decoration: none;">
            <i class="fa-solid fa-arrow-left" style="margin-right: 6px;"></i>Kembali
        </a>

        @auth
        <a href="{{ route('dashboard') }}"
           style="display: block; text-align: center; font-size: 13px; color: var(--auth-text); text-decoration: none; padding: 8px; opacity: 0.7; transition: opacity 0.2s;"
           onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7">
            Ke Dashboard
        </a>
        @endauth
    </div>

</div>
@endsection
