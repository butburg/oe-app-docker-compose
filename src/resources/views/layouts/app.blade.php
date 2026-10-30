<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>


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

        <!-- Page Heading, only for screen readers and search engines, the navigation shows where you are -->
        @if (isset($header))
            <header class="sr-only">
                {{ $header }}
            </header>
        @endif

        @if (Session::has('notif.success'))
            <div class="mx-auto max-w-screen-2xl px-4 pt-3 sm:px-6 lg:px-8" role="status" aria-live="polite">
                <div class="flex items-start gap-2 rounded-md border border-c-accent/30 bg-c-accent/10 px-3 py-2 text-sm text-c-text">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-c-accent" aria-hidden="true" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.78-9.97a.75.75 0 0 0-1.06-1.06l-3.47 3.47-1.47-1.47a.75.75 0 1 0-1.06 1.06l2 2a.75.75 0 0 0 1.06 0l4-4Z" clip-rule="evenodd" />
                    </svg>
                    <span class="min-w-0 break-words">{{ Session::get('notif.success') }}</span>
                </div>
            </div>
        @endif

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>

        <footer class="mt-6 border-t border-c-line/50 pb-12 pt-6 text-center text-sm text-c-muted sm:mt-8 sm:pb-16 sm:pt-8">
            <div class="mx-auto flex w-full max-w-2xl flex-col items-center gap-10 px-4">
                {{-- support, the button frame is the one of x-glow-button --}}
                <div class="flex flex-col items-center gap-4">
                    <p class="max-w-sm leading-relaxed">
                        Weedy Gallery is a hobby project without ads. If you enjoy it, an ice cream keeps it going.
                    </p>
                    <a class="group inline-block rounded-lg bg-gradient-to-r from-c-primary via-c-secondary to-c-accent p-0.5 shadow-lg shadow-c-primary/20 transition hover:shadow-c-secondary/30 focus:outline-none focus-visible:ring-2 focus-visible:ring-c-accent focus-visible:ring-offset-2 focus-visible:ring-offset-c-background"
                        href="https://ko-fi.com/butburg" target="_blank" rel="noopener">
                        <span class="flex min-h-11 items-center justify-center gap-2 rounded-md bg-c-surface px-5 font-semibold text-c-text transition-colors group-hover:bg-transparent group-hover:text-c-background">
                            {{-- ice cream cone --}}
                            <svg class="h-5 w-5 transition-transform duration-300 group-hover:-rotate-12" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" aria-hidden="true">
                                <path d="M6.5 10.5a5.5 5.5 0 1 1 11 0" />
                                <path d="M5.5 10.5h13" />
                                <path d="M7 10.5 12 21l5-10.5" />
                                <path d="M8.6 13.8h6.8" />
                            </svg>
                            Say thank you
                        </span>
                    </a>
                </div>

                <nav class="flex flex-wrap items-center justify-center gap-x-3 gap-y-2" aria-label="Footer">
                    <a class="font-medium text-c-primary underline underline-offset-4 hover:text-c-accent"
                        href="{{ route('about') }}">About this site</a>
                    <span class="text-c-quiet" aria-hidden="true">·</span>
                    <a class="font-medium text-c-primary underline underline-offset-4 hover:text-c-accent"
                        href="{{ route('impressum') }}">Impressum & Datenschutz</a>
                </nav>

                <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-3 text-xs text-c-quiet">
                    {{-- the tilting smiley is the signature of the site, keep it --}}
                    <a class="group inline-flex items-center gap-2 rounded-xl px-2 py-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary"
                        href="https://ko-fi.com/butburg" target="_blank" rel="noopener"
                        aria-label="Made with love by EW">
                        <span
                            class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-c-surface-raised text-c-muted transition-colors group-hover:text-c-accent">
                            <svg class="h-6 w-6 rotate-180 transform transition-transform duration-300 group-hover:rotate-[225deg]"
                                viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                                aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8" />
                                <circle cx="8.5" cy="10" r="1" fill="currentColor" />
                                <circle cx="15.5" cy="10" r="1" fill="currentColor" />
                                <path d="M8 14.2C9 15.7 10.2 16.3 12 16.3C13.8 16.3 15 15.7 16 14.2"
                                    stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-c-primary/70 transition-colors duration-300 group-hover:text-c-accent">
                            Made with love by EW
                        </span>
                    </a>
                    <a class="opacity-60 transition-opacity hover:opacity-100 focus:outline-none focus-visible:opacity-100 focus-visible:ring-2 focus-visible:ring-c-primary"
                        href="https://www.lima-city.de/webhosting?cref=439120" target="_blank" rel="noopener"
                        title="Hosted at lima-city">
                        <img class="h-6" alt="lima-city: Webhosting, Domains und Cloud"
                            src="{{ asset('images/lima-city-button.jpg') }}">
                    </a>
                </div>

                {{-- major version for everyone, full details only for dev and admins --}}
                <p class="text-c-primary/35">
                    Laravel v{{ Str::before(app()->version(), '.') }}
                    @if (app()->environment('local') || auth()->user()?->usertype === 'admin')
                        @php($vitePackage = base_path('node_modules/vite/package.json'))
                        <br>
                        Laravel: {{ app()->version() }}<br>
                        PHP: {{ PHP_VERSION }}<br>
                        @if (function_exists('shell_exec'))
                            Composer: {{ shell_exec('composer --version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}<br>
                        @endif
                        @if (file_exists($vitePackage))
                            Vite: {{ json_decode(file_get_contents($vitePackage))->version }}<br>
                        @endif
                        @if (class_exists('SQLite3'))
                            SQLite: {{ SQLite3::version()['versionString'] }}
                        @endif
                    @endif
                </p>
            </div>
        </footer>

    </div>
</body>

</html>
