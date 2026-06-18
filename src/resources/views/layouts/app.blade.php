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
                <div>
                    <a class="group inline-flex items-center justify-center gap-3 rounded-xl px-3 py-2"
                        href="https://buymeacoffee.com/butburg" target="_blank" rel="noopener"
                        aria-label="Made with love by EW">
                        <span
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-c-primary/10 text-c-primary/35 transition-all duration-300 group-hover:bg-c-primary/20 group-hover:text-c-accent">
                            <svg class="h-8 w-8 rotate-180 transform transition-transform duration-300 group-hover:rotate-[225deg]"
                                viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8" />
                                <circle cx="8.5" cy="10" r="1" fill="currentColor" />
                                <circle cx="15.5" cy="10" r="1" fill="currentColor" />
                                <path d="M8 14.2C9 15.7 10.2 16.3 12 16.3C13.8 16.3 15 15.7 16 14.2"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                        </span>

                        <span class="text-left">
                            <span
                                class="block text-base font-semibold tracking-wide text-c-primary/70 underline decoration-c-primary/50 underline-offset-4 transition-colors duration-300 group-hover:text-c-accent group-hover:decoration-c-accent/90">
                                Made with Love
                            </span>
                            <span
                                class="block text-base leading-relaxed text-c-primary/70 transition-colors duration-300 group-hover:text-c-accent">by
                                EW</span>
                        </span>
                    </a>
                </div>

                <div class="flex flex-col items-center space-y-2">
                    <p class="text-xs leading-relaxed text-c-primary/70">
                        If you want to host your own site, you can support this project by using my Lima-City link.
                    </p>
                    <a class="inline-flex w-full justify-center" href="https://www.lima-city.de/webhosting?cref=439120"
                        target="_blank" rel="noopener">
                        <img class="mx-auto h-10" alt="lima-city: Webhosting, Domains und Cloud"
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
