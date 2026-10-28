@php
    // milestones taken from the commit history and branches of the GitHub repositories
    $link = 'class="font-medium text-c-primary underline underline-offset-4 hover:text-c-accent" target="_blank" rel="noopener"';

    // texts are static and may contain links, so they are rendered unescaped
    $milestones = [
        [
            'date' => 'Until 2022',
            'title' => 'Oh Eweedy, the Bilder Blog',
            'text' => 'This is how the gallery looked and what it was called until 2022: "Oh Eweedy – Bilder Blog", written in German and built in plain PHP. You could sign up, upload pictures, leave comments and find the blog on Facebook and Tumblr. The oldest piece of code I found so far is the gallery, version 0.2 from May 2015. It already used <a ' . $link . ' href="https://jquery.com">jQuery</a> for the first time, and later <a ' . $link . ' href="https://lokeshdhakar.com/projects/lightbox2/">Lightbox</a> let you click a picture to see it big and flip through the gallery.',
            'images' => [
                ['file' => '2022-plain-php.webp', 'width' => 1471, 'height' => 979, 'caption' => 'Oh Eweedy – Bilder Blog, March 2022'],
            ],
        ],
        [
            'date' => '2020',
            'title' => 'Learning the craft',
            'text' => 'During my studies at <a ' . $link . ' href="https://www.htw-berlin.de">HTW Berlin</a> I built my first apps with <a ' . $link . ' href="https://rubyonrails.org">Ruby on Rails</a>. I carried the patterns I learned there over into my plain and cluttered PHP site and reworked it: a proper, hand-written structure with shared blocks like the head and navigation pulled in via <a ' . $link . ' href="https://www.php.net/manual/en/function.require-once.php">require_once</a>, so each page only switches on what it needs. I worked on login, dark mode, more automation. And a new design, like always.',
        ],
        [
            'date' => 'October 2023',
            'title' => 'Discovering Laravel',
            'text' => 'I started to learn <a ' . $link . ' href="https://laravel.com">Laravel</a> and play around with it. It felt familiar: like Ruby on Rails for <a ' . $link . ' href="https://www.ruby-lang.org">Ruby</a>, Laravel is a framework full of patterns for building websites, only with PHP. It follows the "convention over configuration" philosophy, so a lot just works with smart defaults instead of endless setup. My hand-written includes would become <a ' . $link . ' href="https://laravel.com/docs/blade">Blade</a> templates with layouts and components. The plan: move the site from plain PHP to Laravel one day. Along the way I tried out automated tests and a build pipeline on GitHub.',
        ],
        [
            'date' => 'March 2024',
            'title' => 'The old site goes on GitHub',
            'text' => 'The PHP site moved into the "oheweedy" repository and got an automatic FTP upload. A new footer and image scaling followed. In July a small script exported all image blobs from the database to files, the first step of the migration.',
        ],
        [
            'date' => 'April 2024',
            'title' => 'Laravel 11 in containers',
            'text' => '"oe-app-11" tried Laravel 11 with Breeze, Sail, MySQL and a dev container for GitHub Codespaces. The setup was worth sharing, so it became its own template: <a ' . $link . ' href="https://github.com/butburg/laravel-11-sail-codespace">laravel-11-sail-codespace</a>.',
        ],
        [
            'date' => 'April – July 2024',
            'title' => 'The home of this site',
            'text' => 'On 29 April 2024 the work moved to "oe-app-docker-compose", built on Andrew Schmelyun\'s docker-compose setup for Laravel. May brought image uploads with resizing, email verification, the first tests and a simple gallery. In July a GitHub Action started deploying to Lima-City and version 1.0 brought over every post from the old site.',
        ],
        [
            'date' => '2024 – 2025',
            'title' => 'Weedy Gallery grows up',
            'text' => 'Feature by feature the gallery grew: pagination, image variants for faster loading, the privacy pages, drafts and sensitive posts, user profiles, and comments only visible to members.',
            'images' => [
                ['file' => '2024-laravel-desktop.webp', 'width' => 1900, 'height' => 960, 'caption' => 'The first Laravel design on desktop'],
                ['file' => '2024-laravel-mobile.webp', 'width' => 498, 'height' => 915, 'caption' => 'The same design on mobile', 'narrow' => true],
            ],
        ],
        [
            'date' => '2026',
            'title' => 'A fresh coat of paint',
            'text' => 'Version 6.0 in March added image zoom, a lock icon for sensitive posts and stronger contrast. In October the site got a complete redesign: a new layout and navigation, gallery search, a polished mobile view and freely licensed demo data for development.',
            'images' => [
                ['file' => '2026-redesign-desktop.webp', 'width' => 1078, 'height' => 887, 'caption' => 'The redesign, October 2026'],
            ],
        ],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-header2>About</x-header2>
    </x-slot>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- easter egg: clicking the intro (not its links) lets the party hamsters rise in the navigation --}}
        <div class="app-surface p-4 sm:p-6" x-data @click="$event.target.closest('a') || $dispatch('hamster-party')">
            <h1 class="text-2xl font-semibold text-c-text">How this site came to be</h1>
            <p class="mt-3 leading-relaxed text-c-muted">
                Oh Eweedy started as a small programming experiment. After building some pages locally (starting
                with the red div...), I heard about
                <a class="font-medium text-c-primary underline underline-offset-4 hover:text-c-accent"
                    href="https://www.bplaced.net" target="_blank" rel="noopener">bplaced</a>.
                It was exactly what I needed (a
                <a class="font-medium text-c-primary underline underline-offset-4 hover:text-c-accent"
                    href="https://www.lima-city.de/webhosting?cref=439120" target="_blank" rel="noopener">web host</a>)
                to make the page publicly available, just like all those other "real pages" on the so-called internet.
                Piece by piece I managed to get it online, for free. How? I
                <a class="font-medium text-c-primary underline underline-offset-4 hover:text-c-accent"
                    href="https://stackoverflow.com" target="_blank" rel="noopener">read stuff on the internet</a>
                about the internet and focused on
                <a class="font-medium text-c-primary underline underline-offset-4 hover:text-c-accent"
                    href="https://www.php.net/docs.php" target="_blank" rel="noopener">PHP</a>
                and
                <a class="font-medium text-c-primary underline underline-offset-4 hover:text-c-accent"
                    href="https://filezilla-project.org" target="_blank" rel="noopener">FTP and SSH uploads</a>.
                Weird stuff, it was. Now it has become what you are looking at. Below I collected some of the
                milestones. The oldest ones are not included, since I can't find them right now. Cheers!
            </p>
        </div>

        <ol class="relative mt-8 space-y-8 border-l border-c-line pl-6 sm:pl-8">
            @foreach ($milestones as $milestone)
                <li class="relative">
                    <span class="absolute -left-[31px] top-1.5 h-3 w-3 rounded-full border-2 border-c-background bg-c-accent sm:-left-[39px]"
                        aria-hidden="true"></span>

                    <p class="text-sm font-semibold uppercase tracking-wide text-c-accent">{{ $milestone['date'] }}</p>
                    <h2 class="mt-1 text-lg font-semibold text-c-text">{{ $milestone['title'] }}</h2>
                    <p class="mt-2 leading-relaxed text-c-muted">{!! $milestone['text'] !!}</p>

                    @if (!empty($milestone['images']))
                        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start">
                            @foreach ($milestone['images'] as $image)
                                <figure class="{{ !empty($image['narrow']) ? 'w-1/2 sm:w-1/4' : 'min-w-0 flex-1' }}">
                                    <img class="h-auto w-full rounded-md border border-c-line"
                                        src="{{ asset('images/about/' . $image['file']) }}"
                                        alt="Screenshot: {{ $image['caption'] }}" width="{{ $image['width'] }}"
                                        height="{{ $image['height'] }}" loading="lazy" data-protected>
                                    <figcaption class="mt-2 text-xs text-c-quiet">{{ $image['caption'] }}</figcaption>
                                </figure>
                            @endforeach
                        </div>
                    @endif
                </li>
            @endforeach
        </ol>

        <div class="app-surface-accent mt-10 p-4 sm:p-6">
            <h2 class="text-lg font-semibold text-c-text">Keep it going</h2>
            <p class="mt-2 leading-relaxed text-c-muted">
                Weedy Gallery is a hobby project without ads. If you enjoy it, you can
                <a class="font-medium text-c-primary underline underline-offset-4 hover:text-c-accent"
                    href="https://ko-fi.com/butburg" target="_blank" rel="noopener">buy me an ice cream on Ko-fi</a>.
            </p>
        </div>
    </div>
</x-app-layout>
