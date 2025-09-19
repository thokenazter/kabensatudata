<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PKM Kaben - Satu Data</title>
    <link rel="icon" type="image/png" href="{{ asset('images/iconsatudata.PNG') }}">
    <link rel="shortcut icon" href="{{ asset('images/iconsatudata.PNG') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('images/iconsatudata.PNG') }}">

    <script>
        const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
    </script>

    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    @include('includes.style')

</head>
<body>
    @yield('content')
    
    @auth
    <!-- Tidak ada lagi komponen Smart Assistant di sini -->
    @endauth
    
    <!-- Chart Scripts -->
    @include('includes.script')

    <!-- Tidak ada lagi script Smart Assistant di sini -->
    @include('components.floating-chatbot')
    
    @stack('scripts')
</body>
</html>
