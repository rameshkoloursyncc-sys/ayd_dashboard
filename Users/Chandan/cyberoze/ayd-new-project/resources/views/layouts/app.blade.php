<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AYD Subscription Management</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpeg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/dashboard/src/style.css'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen flex flex-col">
    @include('partials.navbar')
    <div class="flex flex-grow overflow-hidden bg-gray-50 dark:bg-gray-900">
        @include('partials.sidebar')
        <div id="main-content" class="relative w-full flex flex-col overflow-y-auto bg-gray-50 lg:ml-64 dark:bg-gray-900">
            <main class="flex-grow pt-16">
                @yield('content')
            </main>
            @include('partials.footer')
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    @stack('modals')
</body>
</html>