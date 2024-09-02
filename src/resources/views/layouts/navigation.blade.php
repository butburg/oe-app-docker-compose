<nav class="border-b border-gray-100 bg-c-background text-c-text" x-data="{ open: false }">
    <!-- Navigation Headbar -->
    <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            {{-- Left navigation side --}}
            <div class="flex">
                @auth
                    <!-- Logo -->
                    <div class="flex shrink-0 items-center">
                        <a href="{{ route('dashboard') }}">
                            <x-application-logo class="h-14" />
                        </a>
                    </div>
                    {{-- left nav links logged in --}}
                    <div class="mx-5 flex space-x-8 sm:-my-px">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ config('app.name', 'Start') }}
                        </x-nav-link>
                        <x-nav-link :href="route('posts.index')" :active="request()->routeIs('posts.index')">
                            {{ 'My Images' }}
                        </x-nav-link>
                    </div>
                @else
                    <!-- Logo -->
                    <div class="flex shrink-0 items-center">
                        <a href="{{ route('welcome') }}">
                            <x-application-logo class="h-14" />
                        </a>
                    </div>
                    {{-- left nav links logged out --}}
                    <div class="mx-5 flex space-x-8 sm:-my-px">
                        <x-nav-link :href="route('login')" :active="request()->routeIs('login')">
                            {{ __('Log in') }}
                        </x-nav-link>

                        <x-nav-link :href="route('register')" :active="request()->routeIs('register')">
                            {{ __('Register') }}
                        </x-nav-link>
                    </div>
                @endauth
            </div>

            <!-- right navigation side: Dropdown -->
            @auth
                <!-- Hamburger for small screen -->
                <div class="ms-6 flex items-center sm:hidden">
                    <x-dropdown align="right" width="48" contentClasses="py-1 bg-c-primary">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center justify-center rounded-md p-2 text-c-text transition duration-150 ease-in-out hover:bg-c-background hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
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
                            <div class="space-y-1">
                                <div class="block w-full border-b-2 py-2 pe-4 ps-3 text-start text-base font-medium">
                                    <div class="text-base font-medium">{{ Str::limit(Auth::user()->name, 30, '...') }}</div>
                                </div>
                                @if (Auth::user()->usertype == 'admin')
                                    <x-responsive-nav-link :href="route('admin.index')">
                                        {{ __('Admin Dashboard') }}
                                    </x-responsive-nav-link>
                                @endif
                                <x-responsive-nav-link :href="route('profile.edit')">
                                    {{ __('Profile') }}
                                </x-responsive-nav-link>

                                <!-- Authentication -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-responsive-nav-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                        {{ __('Log Out') }}
                                    </x-responsive-nav-link>
                                </form>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                {{-- user menu for desktop --}}
                <div class="hidden sm:ms-6 sm:flex sm:items-center">
                    <x-dropdown align="right" width="48" contentClasses="py-1 bg-c-primary">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 text-c-text transition duration-150 ease-in-out hover:bg-c-primary/20 focus:outline-none">
                                <div>{{ Str::limit(Auth::user()->name, 30, '...') }}</div>

                                <div class="ms-1">
                                    <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            @if (Auth::user()->usertype == 'admin')
                                <x-dropdown-link :href="route('admin.index')">
                                    {{ __('Admin Dashboard') }}
                                </x-dropdown-link>
                            @endif
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            @endauth
        </div>
    </div>
</nav>
