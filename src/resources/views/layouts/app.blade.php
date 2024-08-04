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

    <!-- Favicons -->
    <link type="image/x-icon" href="{{ asset('favicons/favicon.ico') }}" rel="shortcut icon">
    <link href="{{ asset('favicons/apple-touch-icon.png') }}" rel="apple-touch-icon" sizes="180x180">
    <link type="image/png" href="{{ asset('favicons/favicon-16x16.png') }}" rel="icon" sizes="16x16">
    <link type="image/png" href="{{ asset('favicons/favicon-32x32.png') }}" rel="icon" sizes="32x32">
    <link href="{{ asset('favicons/site.webmanifest') }}" rel="manifest">
    <meta name="msapplication-TileColor" content="#032226">
    <meta name="theme-color" content="#032226">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-c-background font-sans antialiased">
    <div class="min-h-screen">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @if (isset($header))
            <header class="bg-c-primary/10 text-c-text">
                <div class="mx-auto max-w-screen-2xl px-4 py-3 shadow sm:px-6 lg:px-8">
                    {{ $header }}

                </div>
            </header>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>

        <footer class="text-content-text py-16 text-center text-sm">
            <p>
                <a class="font-medium text-c-primary underline hover:no-underline" href="{{ route('impressum') }}">
                    Impressum & Datenschutz
                </a>
            </p>
            <p class="text-c-primary/30">Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                @if (app()->environment('local'))
                    <br>
                    Laravel: {{ app()->version() }}<br>
                    PHP {{ PHP_VERSION }}<br>
                    Composer: {{ shell_exec('composer --version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}<br>
                    npm: {{ shell_exec('npm --version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}<br>
                    Vite: {{ shell_exec('npm show vite version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}<br>
                    SQLite: {{ shell_exec('sqlite3 --version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}
                @endif
            <p>
        </footer>

    </div>
</body>

</html>
