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

        <footer class="py-16 text-center text-sm text-c-text">
            <div class="mx-auto flex w-full max-w-2xl flex-col items-center gap-8 px-4">
                <div class="space-y-2">
                    <p class="text-base font-semibold tracking-wide text-c-accent">
                        <a class="underline decoration-c-accent/90 underline-offset-4 hover:text-c-primary"
                            href="https://buymeacoffee.com/butburg" target="_blank" rel="noopener">
                            Made with love by EW
                        </a>
                    </p>
                    <a href="https://buymeacoffee.com/butburg" target="_blank" rel="noopener"
                        aria-label="Support this project on Buy Me a Coffee">
                        <img class="h-11" alt="Buy Me a Coffee"
                            src="https://cdn.buymeacoffee.com/buttons/v2/default-yellow.png">
                    </a>
                </div>

                <div class="space-y-2">
                    <p class="text-xs leading-relaxed text-c-primary/70">
                        If you want to host your own site, you can support this project by using my Lima-City link.
                    </p>
                    <a href="https://www.lima-city.de/webhosting?cref=439120" target="_blank" rel="noopener">
                        <img class="h-10" alt="lima-city: Webhosting, Domains und Cloud"
                            src="https://www.lima-city.de/assets/banner/button3.jpg">
                    </a>
                </div>

                <p>
                    <a class="font-medium text-c-primary underline underline-offset-4 hover:text-c-accent"
                        href="{{ route('impressum') }}">
                        Impressum & Datenschutz
                    </a>
                </p>

                <p class="text-c-primary/35">
                    Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
                    @if (app()->environment('local'))
                        <br>
                        Laravel: {{ app()->version() }}<br>
                        PHP {{ PHP_VERSION }}<br>
                        Composer: {{ shell_exec('composer --version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}<br>
                        npm: {{ shell_exec('npm --version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}<br>
                        Vite: {{ shell_exec('npm show vite version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}<br>
                        SQLite: {{ shell_exec('sqlite3 --version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}
                    @endif
                </p>
            </div>
        </footer>

    </div>
</body>

</html>
