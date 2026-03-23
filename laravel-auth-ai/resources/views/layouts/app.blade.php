<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'SecureAuth') — AI Auth System</title>
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

    @yield('content')


    @stack('scripts')

</body>

</html>
