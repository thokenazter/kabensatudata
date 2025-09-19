<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pemetaan Kesehatan Keluarga</title>
    <link rel="manifest" href="/manifest.webmanifest">
    @php
        $canViewSensitiveHealth = auth()->check() && auth()->user()->hasAnyRole(['nakes', 'super_admin']);
    @endphp
    <script>
        window.__canViewSensitiveHealth = {{ $canViewSensitiveHealth ? 'true' : 'false' }};
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
<div id="app"></div>
</body>
</html>
