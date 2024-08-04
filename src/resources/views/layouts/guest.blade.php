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

<body class="bg-c-background font-sans text-c-text antialiased">
    <div class="flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0">
        <div>
            <a href="{{ route('welcome') }}">
                <x-application-logo class="h-32" />
            </a>
        </div>
        @include('layouts.navigation')
        <div class="mt-6 w-full overflow-hidden bg-c-primary/80 px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg">
            {{ $slot }}
        </div>
        @env('local')
        <x-login-link
            class="m-3 inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900" />
        @endenv
    </div>

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
</body>

</html>
