<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.bunny.net" rel="preconnect">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-c-background">
    <div class="bg-body-bg min-h-screen">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
            <header class=" bg-c-primary/10 text-c-text">
                <div class="mx-auto max-w-screen-2xl px-4 py-3 shadow sm:px-6 lg:px-8">
                    {{ $header }}

                    {{-- check if there is a notif.success flash session --}}
                    @if (Session::has('notif.success'))
                        <div class="mt-2 rounded-lg bg-blue-300 p-4">
                            {{-- if it's there then print the notification --}}
                            <span class="text-white">{{ Session::get('notif.success') }}</span>
                        </div>
                    @endif

                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
</body>

</html>
