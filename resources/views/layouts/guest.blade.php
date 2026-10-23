<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'GhostGear Rental') }}</title>
        <!-- Browser Tab Icon (Favicon) -->
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=20261023c">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=20261023c">
        <link rel="icon" type="image/png" href="{{ asset('images/logo-icon.png') }}?v=20261023c">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=20261023c">
        <link rel="apple-touch-icon" href="{{ asset('images/logo-icon.png') }}?v=20261023c">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#0B0F14] text-[#EBF2F8] antialiased min-h-screen flex flex-col justify-center items-center p-4">
        {{ $slot }}
    </body>
</html>
