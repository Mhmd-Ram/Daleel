<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Events')  ·  {{ config('app.name') }}</title>
    {{-- Mark JS as available before paint so reveal states never flash. --}}
    <script>document.documentElement.classList.add('js')</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
</head>
<body class="min-h-full bg-transparent text-stone-800 antialiased flex flex-col">
    @include('partials.backdrop')

    <div data-scroll-sentinel aria-hidden="true" class="absolute top-0 h-px w-px"></div>

    <header data-header class="sticky top-0 z-50 border-b border-stone-200/70 bg-white/70 backdrop-blur">
        <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-6 px-4">
            <a href="{{ route('home') }}" class="group flex items-center gap-2 font-semibold tracking-tight text-stone-900">
                <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-600 text-sm font-bold text-white shadow-sm shadow-emerald-600/30 transition-transform duration-300 group-hover:-rotate-6 group-hover:scale-105">E</span>
                <span>{{ config('app.name') }}</span>
            </a>

            <button type="button" data-menu-toggle aria-expanded="false" aria-controls="main-menu" aria-label="Toggle menu"
                    class="group flex items-center gap-3 rounded-full border border-stone-200 bg-white/80 py-1.5 pl-4 pr-2 text-sm font-medium text-stone-700 shadow-sm transition hover:border-stone-300 hover:shadow-md">
                <span>Menu</span>
                <span class="relative grid h-7 w-7 place-items-center rounded-full bg-stone-900 text-white">
                    <span class="relative block h-3 w-4">
                        <span class="bar bar-top absolute left-0 top-0 h-0.5 w-4 rounded bg-current"></span>
                        <span class="bar bar-mid absolute left-0 top-[5px] h-0.5 w-4 rounded bg-current"></span>
                        <span class="bar bar-bot absolute left-0 top-2.5 h-0.5 w-4 rounded bg-current"></span>
                    </span>
                </span>
            </button>
        </nav>
    </header>

    {{-- Full-screen overlay menu (desktop + mobile) --}}
    <div data-menu id="main-menu" role="dialog" aria-modal="true" aria-label="Site menu">
        <div class="menu-panel"></div>
        <div class="relative z-10 mx-auto flex h-full max-w-6xl flex-col justify-center px-6 sm:px-10">
            <p class="menu-item mb-10 inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.22em] text-emerald-700">
                <x-icon name="sparkles" class="h-4 w-4" /> Where to next
            </p>

            <nav class="flex flex-col gap-3 sm:gap-4">
                <div class="menu-item"><x-menu-link :href="route('home')" label="Home" /></div>
                <div class="menu-item"><x-menu-link :href="route('events.index')" label="Events" /></div>

                @auth
                    <div class="menu-item"><x-menu-link :href="route('my-events')" label="My calendar" /></div>
                    @if (auth()->user()->isOrganizer())
                        <div class="menu-item"><x-menu-link :href="route('organizer.events.index')" label="Organize" /></div>
                    @endif
                    <div class="menu-item"><x-menu-link :href="route('profile.show')" label="Profile" /></div>
                    <div class="menu-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="menu-link">
                                <span class="menu-word">Log out<span class="menu-link__fill" aria-hidden="true">Log out</span></span>
                                <x-icon name="arrow-right" class="menu-link__arrow h-7 w-7" />
                            </button>
                        </form>
                    </div>
                @else
                    <div class="menu-item"><x-menu-link :href="route('login')" label="Log in" /></div>
                    <div class="menu-item"><x-menu-link :href="route('register')" label="Sign up" /></div>
                @endauth
            </nav>

            <div class="menu-item mt-14 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-stone-500">
                <span class="inline-flex items-center gap-2"><x-icon name="ticket" class="h-4 w-4 text-emerald-600" /> {{ config('app.name') }}</span>
                <span>Find and join events near you.</span>
            </div>
        </div>
    </div>

    <main class="relative mx-auto w-full max-w-6xl flex-1 px-4 py-10">
        @include('partials.flash')
        @yield('content')
    </main>

    <footer class="relative border-t border-stone-200/70 bg-white/60 backdrop-blur">
        <div class="mx-auto max-w-6xl px-4 py-6 text-sm text-stone-500">
            {{ config('app.name') }}  ·  Find and join events near you.
        </div>
    </footer>

    {{-- Page-specific bundles (currently the Leaflet map) push themselves here
         so they are not loaded site-wide. --}}
    @stack('scripts')
</body>
</html>
