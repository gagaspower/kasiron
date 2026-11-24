<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#f6f7f9">
        <meta name="description" content="KasirOn membantu mengelola transaksi, produk, dan ringkasan penjualan toko.">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="KasirOn">
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('pwa-192x192.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
        <title>@yield('title', 'Dashboard | KasirOn')</title>
        @fonts('inter')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen font-sans text-slate-900 antialiased">
        @include('partials.dashboard.navbar', ['navigation' => $navigation])

        @yield('content')
    </body>
</html>
