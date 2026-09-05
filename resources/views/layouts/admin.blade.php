<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <title>@yield('title', 'Admin')  ·  {{ config('app.name') }}</title>
    <script>document.documentElement.classList.add('js')</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fonts
</head>
<body class="min-h-full bg-transparent text-stone-800 antialiased flex flex-col">
    @include('partials.backdrop')

    <div data-scroll-sentinel aria-hidden="true" class="absolute top-0 h-px w-px"></div>

    <header data-header class="sticky top-0 z-40 border-b border-stone-700 bg-stone-900 text-stone-100">
        <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-6 px-4">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 font-semibold tracking-tight">
                <img src="{{ asset('logo-64.png') }}" alt="" width="32" height="32" class="h-8 w-8 rounded-lg">
                <span>{{ config('app.name') }} <span class="text-stone-400">{{ __('app.admin.label') }}</span></span>
            </a>

            @auth('admin')
                <div class="flex items-center gap-1 text-sm">
                    <a href="{{ route('admin.dashboard') }}" @class(['nav-link rounded-md px-3 py-2 text-stone-300 transition hover:text-white']) @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>{{ __('app.admin.dashboard') }}</a>
                    <a href="{{ route('admin.events.index') }}" @class(['nav-link rounded-md px-3 py-2 text-stone-300 transition hover:text-white']) @if(request()->routeIs('admin.events.*')) aria-current="page" @endif>{{ __('app.admin.events') }}</a>
                    <a href="{{ route('admin.users.index') }}" @class(['nav-link rounded-md px-3 py-2 text-stone-300 transition hover:text-white']) @if(request()->routeIs('admin.users.*')) aria-current="page" @endif>{{ __('app.admin.users') }}</a>
                    <a href="{{ route('admin.reports.index') }}" @class(['nav-link rounded-md px-3 py-2 text-stone-300 transition hover:text-white']) @if(request()->routeIs('admin.reports.*')) aria-current="page" @endif>{{ __('app.admin.reports') }}</a>
                    <a href="{{ route('admin.categories.index') }}" @class(['nav-link rounded-md px-3 py-2 text-stone-300 transition hover:text-white']) @if(request()->routeIs('admin.categories.*')) aria-current="page" @endif>{{ __('app.admin.categories') }}</a>
                    <a href="{{ route('admin.organizer-applications.index') }}" @class(['nav-link rounded-md px-3 py-2 text-stone-300 transition hover:text-white']) @if(request()->routeIs('admin.organizer-applications.*')) aria-current="page" @endif>{{ __('app.admin.organizers') }}</a>
                    {{-- A form, not a link: this signs the admin into the web
                         guard as well, so it needs CSRF. --}}
                    <form method="POST" action="{{ route('admin.view-site') }}">
                        @csrf
                        <button type="submit" class="nav-link rounded-md px-3 py-2 text-stone-300 transition hover:text-white">{{ __('app.admin.view_site') }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="rounded-md px-3 py-2 text-stone-300 transition hover:bg-stone-800 hover:text-white">{{ __('app.nav.log_out') }}</button>
                    </form>
                </div>
            @endauth
        </nav>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-10">
        @include('partials.flash')
        @yield('content')
    </main>

    {{-- Page-specific bundles (currently the Leaflet map) push themselves here
         so they are not loaded across the whole admin area. --}}
    @stack('scripts')
</body>
</html>
