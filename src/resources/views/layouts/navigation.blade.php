<nav class="border-b border-c-line bg-c-surface text-c-text">
    <!-- Navigation Headbar -->
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between" x-data="{ search: false }">
            {{-- Left navigation side --}}
            <div class="flex">
                <!-- Logo, always leads to the gallery -->
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('dashboard') }}" aria-label="{{ config('app.name') }} home">
                        <x-application-logo class="h-10 w-10" />
                    </a>
                </div>
                @auth
                    {{-- left nav links logged in, below lg they live in the hamburger menu --}}
                    <div class="mx-5 hidden items-center space-x-6 lg:flex">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ config('app.name') }}
                        </x-nav-link>
                        <x-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.index')">
                            {{ __('My Images') }}
                        </x-nav-link>
                        <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">
                            {{ __('Profile') }}
                        </x-nav-link>
                        @if (Auth::user()->usertype == 'admin')
                            <x-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.index')">
                                {{ __('Admin') }}
                            </x-nav-link>
                        @endif
                    </div>
                @else
                    {{-- left nav links logged out --}}
                    <div class="mx-3 flex items-center space-x-3 sm:mx-5 sm:space-x-6" :class="{ 'max-lg:hidden': search }">
                        <x-nav-link class="hidden sm:inline-flex" :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ config('app.name') }}
                        </x-nav-link>
                        <x-nav-link :href="route('login')" :active="request()->routeIs('login')">
                            {{ __('Log in') }}
                        </x-nav-link>
                        <x-nav-link :href="route('register')" :active="request()->routeIs('register')">
                            {{ __('Register') }}
                        </x-nav-link>
                    </div>
                @endauth
            </div>

            {{-- search, below lg it opens from the icon and fills the bar instead of a popup --}}
            <form class="ms-auto flex items-center self-center lg:me-4" :class="{ 'ms-auto': !search, 'flex-1 ms-3': search }"
                action="{{ route('dashboard') }}" method="GET" role="search"
                @click.outside="search = false" @keydown.escape="search = false">
                <label class="sr-only" for="gallery-search">Search gallery</label>
                <div class="relative hidden w-full lg:block lg:w-64" :class="{ 'hidden': !search }">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-c-quiet"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-4-4" />
                    </svg>
                    <x-text-input class="h-10 ps-9 pe-3 text-sm" id="gallery-search" name="q" type="search"
                        :value="request('q')" placeholder="Search gallery" x-ref="search" />
                </div>
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-md text-c-text hover:bg-c-surface-raised focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary lg:hidden"
                    :class="{ 'hidden': search }" @click="search = true; $nextTick(() => $refs.search.focus())"
                    aria-label="Search gallery" title="Search gallery">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-4-4" />
                    </svg>
                </button>
            </form>

            <!-- right navigation side -->
            @auth
                <!-- Hamburger for small screen -->
                <div class="ms-3 flex items-center gap-2 lg:hidden">
                    @unless (request()->routeIs('posts.create', 'posts.edit'))
                        <x-create-post-button />
                    @endunless
                    <x-dropdown align="right" width="48" contentClasses="py-1 bg-c-surface-raised">
                        <x-slot name="trigger">
                            <button type="button" aria-label="Open menu" :aria-expanded="open.toString()"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-md text-c-text transition-colors hover:bg-c-surface-raised focus:outline-none focus-visible:ring-2 focus-visible:ring-c-primary">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path class="inline-flex" :class="{ 'hidden': open, 'inline-flex': !open }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                    <path class="hidden" :class="{ 'hidden': !open, 'inline-flex': open }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                                {{ config('app.name') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.index')">
                                {{ __('My Images') }}
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">
                                {{ __('Profile') }}
                            </x-responsive-nav-link>
                            @if (Auth::user()->usertype == 'admin')
                                <x-responsive-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.index')">
                                    {{ __('Admin') }}
                                </x-responsive-nav-link>
                            @endif

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-responsive-nav-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-responsive-nav-link>
                            </form>

                            <p class="mt-1 truncate border-t border-c-line px-3 pb-1 pt-2 text-xs text-c-quiet"
                                title="{{ Auth::user()->name }}">
                                {{ Str::limit(Auth::user()->name, config('app.truncate_name'), '...') }}
                            </p>
                        </x-slot>
                    </x-dropdown>
                </div>

                {{-- user actions for desktop --}}
                <div class="hidden items-center gap-4 lg:flex">
                    @unless (request()->routeIs('posts.create', 'posts.edit'))
                        <x-create-post-button />
                    @endunless
                    <span class="hidden max-w-40 truncate text-xs text-c-quiet xl:inline" title="{{ Auth::user()->name }}">
                        {{ Str::limit(Auth::user()->name, config('app.truncate_name'), '...') }}
                    </span>
                    <form class="flex self-stretch" method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-nav-link>
                    </form>
                </div>
            @else
                <div class="ms-3 flex items-center">
                    @unless (request()->routeIs('login', 'register', 'password.*'))
                        <x-create-post-button />
                    @endunless
                </div>
            @endauth
        </div>
    </div>
</nav>
