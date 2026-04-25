<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ env('APP_NAME') }} — @yield('title')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/icon/logo-2.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet" />
    
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">

    {{-- TailwindCss --}}
    <script src="https://cdn.tailwindcss.com"></script>


    @stack('styles')

</head>

<body>

<div class="auth-shell">

    {{-- ── SIDEBAR ── --}}
    <aside class="auth-sidebar">
        <img
            src="https://imgs.search.brave.com/8cCFJWwWrANtuYGajwxP9RbbiOWaKs-BMuGc8kcFoI0/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMudW5zcGxhc2gu/Y29tL3Bob3RvLTE2/NTk0NjkzNzc3Njgt/NGY0MmYyZjA5MWM1/P2ZtPWpwZyZxPTYw/Jnc9MzAwMCZhdXRv/PWZvcm1hdCZmaXQ9/Y3JvcCZpeGxpYj1y/Yi00LjEuMCZpeGlk/PU0zd3hNakEzZkRC/OE1IeHpaV0Z5WTJo/OE5IeDhaR0Z5YXlV/eU1HZHlZV1JwWlc1/MGZHVnVmREI4ZkRC/OGZId3c"
            class="sidebar-bg"
            alt="" />

        <div class="sidebar-overlay"></div>

        {{-- Tagline --}}
        <div class="sidebar-tagline">
            <div class="sidebar-accent-bar"></div>
            <p class="sidebar-headline">
                @yield('sidebar_headline', 'SSO Authentication.')
            </p>
            <p class="sidebar-sub">
                @yield('sidebar_sub', 'Single Sign-On (SSO) Simple login for all your applications. Protected by an intelligent AI Risk Engine.')
            </p>
        </div>

    </aside>

    {{-- ── MAIN AREA ── --}}
    <main class="auth-main">

        {{-- Mobile header --}}
        <div class="mobile-header">
            <div class="mobile-logo-icon">
                <img src="https://imgs.search.brave.com/7MJlL-HdJvur9xi9304BQyj1mWDNm7kqsYadjHkOisg/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly/zdGF0/aWMudmVjdGVlenku/Y29tL3N5c3RlbS9y/ZXNvdXJjZXMvdGh1/bWJuYWlscy8wNjcv/MDQ3Lzg3Ni9zbWFs/bC9maWVyeS1jcnlz/dGFsLW0tbGV0dGVy/LWxvZ28tYS1ib2xk/LWFuZC1lbGVnYW50/LWRlc2lnbi1vbi10/cmFuc3BhcmVudC1i/YWNrZ3JvdW5kLXBu/Zy5wbmc" alt="">
            </div>
            <span class="mobile-logo-text">Smart Auth With AI</span>
        </div>

        {{-- Form center --}}
        <div class="auth-center">
            <div class="auth-box">

                {{-- Heading --}}
                <div class="form-heading">
                    <div class="form-accent-bar"></div>
                    <h1 class="form-title">@yield('auth_title')</h1>
                    <p class="form-subtitle">@yield('auth_subtitle')</p>
                </div>

                {{-- AI Protection Banner (Optional) --}}


                {{-- Session Alerts --}}
                @if(session('info'))
                    <div class="alert alert-info">{{ session('info') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                @yield('auth_content')

                {{-- Footer links (e.g. Back to login) --}}
                @if(View::hasSection('auth_footer_extra'))
                    <div class="divider">
                        <div class="divider-line"></div>
                    </div>
                    <div style="text-align: center;">
                        @yield('auth_footer_extra')
                    </div>
                @endif

            </div>
        </div>

        {{-- Footer --}}
        <div class="auth-footer">
            <span class="footer-brand">Mixudev</span>
            <div class="footer-ai">
                <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Protected by <span class="ai-name">&nbsp;AI Risk Engine</span>
            </div>
        </div>

    </main>

</div>

@stack('scripts')

</body>

</html>
