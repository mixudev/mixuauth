<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Auth Monitor — DEV</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
@include('partials.security.styles')
</head>
<body>

{{-- Sidebar Navigation --}}
@include('partials.security.sidebar')

<div class="app-shell">

    {{-- Top Header --}}
    @include('partials.security.header')

    {{-- Stats Bar --}}
    @include('partials.security.stats')

    {{-- All pages pre-rendered, toggled via show/hide --}}
    <main class="page-content">

        <div class="tab-page" id="page-otp">
            @include('admin.security.otp')
        </div>

        <div class="tab-page" id="page-logs" style="display:none">
            @include('admin.security.logs')
        </div>

        <div class="tab-page" id="page-devices" style="display:none">
            @include('admin.security.devices')
        </div>

        <div class="tab-page" id="page-users" style="display:none">
            @include('admin.security.users')
        </div>

        <div class="tab-page" id="page-blacklist" style="display:none">
            @include('admin.security.blacklist')
        </div>

        <div class="tab-page" id="page-whitelist" style="display:none">
            @include('admin.security.whitelist')
        </div>

    </main>

</div>

{{-- Toast notification --}}
<div class="toast" id="toast"></div>

{{-- Confirm / Prompt modal --}}
@include('partials.security.modal')

{{-- Core JS utilities --}}
@include('partials.security.scripts-core')

<script>
// Boot — semua halaman sudah ada di DOM, tinggal load data
document.addEventListener('DOMContentLoaded', function () {
  loadStats();
  loadPage('otp');
  setInterval(loadStats, 10000);
});
</script>

</body>
</html>