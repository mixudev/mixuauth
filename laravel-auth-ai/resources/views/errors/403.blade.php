<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Ditolak</title>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,600;1,400;1,600&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Lora', serif;
            background-color: #fafaf9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
            position: relative;
            overflow: hidden;
        }

        /* Blobs */
        body::before, body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
        }
        body::before {
            top: -8rem; right: -8rem;
            width: 24rem; height: 24rem;
            background: #fee2e2;
            opacity: 0.4;
        }
        body::after {
            bottom: -6rem; left: -6rem;
            width: 20rem; height: 20rem;
            background: #e7e5e4;
            opacity: 0.3;
        }

        /* Card */
        .card-wrap {
            position: relative;
            width: 100%;
            max-width: 28rem;
        }
        .card {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(20px);
            border: 1px solid #f5f5f4;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.1);
            padding: 2.5rem;
            text-align: center;
        }

        /* ── Animations ── */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(-3deg); }
            50%       { transform: translateY(-12px) rotate(3deg); }
        }
        @keyframes walk {
            0%   { transform: translateX(0) scaleX(1); }
            45%  { transform: translateX(60px) scaleX(1); }
            50%  { transform: translateX(60px) scaleX(-1); }
            95%  { transform: translateX(0) scaleX(-1); }
            100% { transform: translateX(0) scaleX(1); }
        }
        @keyframes blink {
            0%, 90%, 100% { transform: scaleY(1); }
            95%            { transform: scaleY(0.1); }
        }
        @keyframes signSwing {
            0%, 100% { transform: rotate(-4deg); }
            50%       { transform: rotate(4deg); }
        }
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .float-anim  { animation: float 3.5s ease-in-out infinite; }
        .walk-anim   { animation: walk 4s ease-in-out infinite; }
        .blink-anim  { animation: blink 3s ease-in-out infinite; transform-origin: 16px 8px; }
        .sign-swing  { animation: signSwing 2.5s ease-in-out infinite; transform-origin: top center; }

        .fade-up   { animation: fadeSlideUp 0.6s 0.0s ease both; }
        .fade-up-1 { animation: fadeSlideUp 0.6s 0.1s ease both; }
        .fade-up-2 { animation: fadeSlideUp 0.6s 0.2s ease both; }
        .fade-up-3 { animation: fadeSlideUp 0.6s 0.35s ease both; }

        /* ── Scene ── */
        .scene {
            position: relative;
            height: 9rem;
            margin-bottom: 1.5rem;
        }
        .ground {
            position: absolute;
            bottom: 0; left: 2rem; right: 2rem;
            height: 1px;
            background: #e7e5e4;
        }

        /* Lock sign */
        .sign-pole-wrap {
            position: absolute;
            right: 3rem;
            bottom: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sign-pole {
            width: 2px;
            height: 4rem;
            background: #d6d3d1;
        }
        .sign-board {
            position: absolute;
            top: 0;
            background: #f87171;
            color: white;
            border-radius: 0.5rem;
            padding: 0.375rem 0.625rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            width: 52px;
        }
        .sign-board svg {
            display: block;
            margin: 0 auto;
            width: 1.25rem;
            height: 1.25rem;
        }
        .sign-code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.625rem;
            font-weight: 600;
            text-align: center;
            margin-top: 0.125rem;
            line-height: 1;
        }

        /* Walker */
        .walker {
            position: absolute;
            bottom: 4px;
            left: 2rem;
            width: 32px;
        }

        /* Question bubble */
        .bubble {
            position: absolute;
            top: 0.5rem;
            left: 4rem;
            width: 2rem;
            height: 2rem;
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .bubble span {
            color: #f59e0b;
            font-weight: 700;
            font-size: 0.875rem;
            font-family: 'Lora', serif;
        }

        /* ── Text ── */
        h1 {
            font-family: 'Lora', serif;
            font-size: 1.875rem;
            font-weight: 600;
            color: #1c1917;
            letter-spacing: -0.025em;
            line-height: 1.25;
            margin-bottom: 0.75rem;
        }
        h1 em { color: #f87171; font-style: italic; }

        .desc {
            color: #a8a29e;
            font-size: 0.875rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            max-width: 260px;
            margin-left: auto;
            margin-right: auto;
            font-family: 'Lora', serif;
        }

        /* ── Buttons ── */
        .btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            background: #1c1917;
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 0.75rem;
            padding: 0.875rem;
            text-decoration: none;
            transition: background 0.2s;
            box-shadow: 0 10px 15px -3px rgba(28,25,23,0.15);
            margin-bottom: 0.75rem;
        }
        .btn-primary:hover { background: #44403c; }
        .btn-primary svg { width: 1rem; height: 1rem; }

        .btn-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }
        .btn-secondary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.375rem;
            border: 1px solid #e7e5e4;
            color: #a8a29e;
            font-weight: 500;
            font-size: 0.75rem;
            border-radius: 0.75rem;
            padding: 0.75rem;
            text-decoration: none;
            transition: background 0.2s, color 0.2s;
            font-family: 'Lora', serif;
        }
        .btn-secondary:hover { background: #fafaf9; color: #44403c; }
        .btn-secondary svg { width: 0.875rem; height: 0.875rem; flex-shrink: 0; }

        /* ── Footer ── */
        .footer-note {
            text-align: center;
            font-size: 0.75rem;
            color: #d6d3d1;
            font-family: 'JetBrains Mono', monospace;
            margin-top: 1.5rem;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>

<div class="card-wrap">
    <div class="card">

        <!-- Scene -->
        <div class="scene fade-up">
            <div class="ground"></div>

            <!-- Lock sign -->
            <div class="sign-pole-wrap">
                <div class="sign-pole"></div>
                <div class="sign-board sign-swing">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    <div class="sign-code">403</div>
                </div>
            </div>

            <!-- Walking person -->
            <div class="walk-anim walker">
                <svg viewBox="0 0 32 48" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:32px;height:48px;">
                    <!-- Head -->
                    <circle cx="16" cy="8" r="6" fill="#1c1917"/>
                    <!-- Eyes -->
                    <g class="blink-anim">
                        <circle cx="13.5" cy="7.5" r="1.2" fill="white"/>
                        <circle cx="18.5" cy="7.5" r="1.2" fill="white"/>
                    </g>
                    <!-- Body -->
                    <rect x="11" y="15" width="10" height="13" rx="3" fill="#44403c"/>
                    <!-- Left arm -->
                    <line x1="11" y1="17" x2="5" y2="24" stroke="#44403c" stroke-width="3" stroke-linecap="round"/>
                    <!-- Right arm (waving) -->
                    <line x1="21" y1="17" x2="27" y2="13" stroke="#44403c" stroke-width="3" stroke-linecap="round"/>
                    <!-- Left leg -->
                    <line x1="13" y1="28" x2="10" y2="40" stroke="#1c1917" stroke-width="3" stroke-linecap="round"/>
                    <!-- Right leg -->
                    <line x1="19" y1="28" x2="22" y2="40" stroke="#1c1917" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>

            <!-- Question bubble -->
            <div class="float-anim bubble">
                <span>?</span>
            </div>
        </div>

        <!-- Text -->
        <h1 class="fade-up-1">Ups, Anda <em>tersesat</em></h1>

        <p class="desc fade-up-2">
            Anda tidak memiliki izin untuk mengakses halaman ini.
        </p>

        <!-- Actions -->
        <div class="fade-up-3">
            <a href="javascript:history.back()" class="btn-primary">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>

            <div class="btn-grid">
                <a href="{{ route('dashboard') }}" class="btn-secondary">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('re-login') }}" class="btn-secondary">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                    </svg>
                    Login Ulang
                </a>
            </div>
        </div>

    </div>

    <p class="footer-note">My App &mdash; Error 403</p>
</div>

</body>
</html>