<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PKM Kaben - Satu Data</title>

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
</body>
    <!-- Chart Scripts -->
    @include('includes.script')
</html>
