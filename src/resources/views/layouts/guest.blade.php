<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ config('app.name', 'Laravel') }}</title>

<!-- Fonts -->
<link href="https://fonts.bunny.net" rel="preconnect">
<link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
    rel="stylesheet" />

<!-- Favicons -->
<link type="image/x-icon" href="{{ asset('favicons/favicon.ico') }}"
    rel="shortcut icon">
<link href="{{ asset('favicons/apple-touch-icon.png') }}" rel="apple-touch-icon"
    sizes="180x180">
<link type="image/png" href="{{ asset('favicons/favicon-16x16.png') }}"
    rel="icon" sizes="16x16">
<link type="image/png" href="{{ asset('favicons/favicon-32x32.png') }}"
    rel="icon" sizes="32x32">
<link href="{{ asset('favicons/site.webmanifest') }}" rel="manifest">
<meta name="msapplication-TileColor" content="#032226">
<meta name="theme-color" content="#032226">

<!-- Scripts -->
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-c-background font-sans text-c-text antialiased">
    <div class="min-h-screen">
        @if ($focused ?? false)
            {{-- only one step left (confirm, change or abort), so no links that leave the page --}}
            <nav class="border-b border-c-line bg-c-surface text-c-text">
                <div class="mx-auto flex h-16 max-w-screen-2xl items-center px-4 sm:px-6 lg:px-8">
                    <x-application-logo class="h-10 w-10" />
                </div>
            </nav>
        @else
            @include('layouts.navigation')
        @endif
        <main class="mx-auto w-full max-w-lg px-4 py-8 sm:py-12">
            <div class="app-surface p-4 sm:p-6">
                {{ $slot }}
            </div>
            @if (app()->environment('local') && !($focused ?? false))
                {{-- seeded user with drafts and published posts, see database/seeders/data/posts.json --}}
                <x-login-link class="app-button-secondary mt-4 w-full" email="user-viele-bilder@user.com" />
                {{-- logs in the first admin, or creates one if there is none --}}
                <x-login-link class="app-button-secondary mt-2 w-full" :user-attributes="['usertype' => 'admin']" label="Developer Login (Admin)" />
                {{-- seeded unverified user (needs db:seed, otherwise the link creates a verified one), shows the guest view with the verify hint --}}
                <x-login-link class="app-button-secondary mt-2 w-full" email="user-unverified@user.com" label="Developer Login (Unverified)" />
            @endif

            <div class="app-surface-accent mt-6 p-4 sm:p-6">
                <div class="text-center">
                    <h1 class="text-2xl font-bold">Weedy Gallery</h1>
                    <h2 class="mb-4 font-bold">The Ultimate Art Platform</h2>
                </div>
                <p class="mb-4">Share your masterpieces and create a collaborative
                    image gallery.</p>

                <ul class="list-disc space-y-2 pl-5">
                    <li><span class="font-semibold">Upload</span> your images and
                        add them to the gallery.</li>
                    <li><span class="font-semibold">Give</span> your piece a name,
                        turning it into a true work of art.</li>
                    <li><span class="font-semibold">Praise</span> other pictures to
                        make someone’s day... or not.</li>
                    <li><span class="font-semibold">Comment</span> to share your
                        profound insights (or witty remarks) with the artist.</li>
                </ul>
            </div>
        </main>
    </div>

    <footer class="border-t border-c-line py-6 text-center text-xs text-c-quiet">
        <a class="text-c-muted underline underline-offset-4 hover:text-c-primary" href="{{ route('about') }}">
            About this site
        </a>
        <br>
        <a class="text-c-muted underline underline-offset-4 hover:text-c-primary" href="{{ route('impressum') }}">
            Impressum & Datenschutz
        </a>
        <p class="mt-3">
            Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
            @if (app()->environment('local'))
                <br>
                Laravel: {{ app()->version() }}<br>
                PHP {{ PHP_VERSION }}<br>
                Composer:
                {{ shell_exec('composer --version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}<br>
                npm:
                {{ shell_exec('npm --version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}<br>
                Vite:
                {{ shell_exec('npm show vite version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}<br>
                SQLite:
                {{ shell_exec('sqlite3 --version | grep -o -E "[0-9]+\.[0-9]+\.[0-9]+"') }}
            @endif
        </p>
    </footer>
</body>

</html>
