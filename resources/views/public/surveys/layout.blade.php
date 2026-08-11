<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pesquisa') — CIOP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ciop-survey.css') }}?v=1">
    @stack('styles')
</head>
<body class="ciop-survey-body">
    <main class="ciop-survey-shell">
        <header class="ciop-survey-brand">
            <a href="{{ url('/') }}">CIOP</a>
        </header>
        @yield('content')
    </main>
</body>
</html>
