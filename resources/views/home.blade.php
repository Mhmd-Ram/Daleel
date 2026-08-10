@extends('layouts.app')

@section('title', 'Discover events near you')

@section('content')
    {{-- Hero --}}
    <section class="grid items-center gap-10 py-4 lg:grid-cols-2 lg:gap-12 lg:py-10">
        <div class="reveal">
            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50/80 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                <x-icon name="sparkles" class="h-4 w-4" /> {{ $stats['events'] }} upcoming events
            </span>

            <h1 class="font-display mt-5 text-5xl uppercase leading-[0.92] tracking-tight text-stone-900 sm:text-6xl lg:text-7xl">
                Go where<br><span class="text-emerald-600">it's happening</span>
            </h1>

            <p class="mt-5 max-w-md text-lg leading-relaxed text-stone-600">
                Discover concerts, talks, and meetups near you, then register in a couple of clicks.
            </p>

            <div class="mt-7 flex flex-wrap items-center gap-3">
                <a href="{{ route('events.index') }}"
                   class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-6 py-3 font-medium text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-700 hover:shadow-emerald-600/40 active:scale-[0.98]">
                    Browse events <x-icon name="arrow-right" class="h-5 w-5" />
                </a>
                @auth
                    <a href="{{ route('my-events') }}" class="inline-flex items-center gap-2 rounded-full border border-stone-300 bg-white/80 px-6 py-3 font-medium text-stone-700 transition hover:border-stone-400 hover:shadow-sm">
                        My events
                    </a>
                @else
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full border border-stone-300 bg-white/80 px-6 py-3 font-medium text-stone-700 transition hover:border-stone-400 hover:shadow-sm">
                        Create account
                    </a>
                @endauth
            </div>

            <dl class="mt-9 flex gap-10">
                <div>
                    <dt class="font-display text-3xl text-stone-900">{{ $stats['events'] }}</dt>
                    <dd class="text-sm text-stone-500">Upcoming events</dd>
                </div>
                <div>
                    <dt class="font-display text-3xl text-stone-900">{{ $stats['categories'] }}</dt>
                    <dd class="text-sm text-stone-500">Categories</dd>
                </div>
            </dl>
        </div>

        {{-- Image collage --}}
        <div class="reveal reveal--right relative hidden h-[420px] lg:block" style="--reveal-delay: 120ms" aria-hidden="true">
            <div class="absolute left-0 top-6 h-56 w-44 -rotate-6 overflow-hidden rounded-2xl border-4 border-white shadow-xl shadow-stone-300/50">
                <img src="https://picsum.photos/seed/eventhub-a/360/440" alt="" class="h-full w-full object-cover">
            </div>
            <div class="absolute right-2 top-0 h-64 w-52 rotate-3 overflow-hidden rounded-2xl border-4 border-white shadow-xl shadow-stone-300/50">
                <img src="https://picsum.photos/seed/eventhub-b/420/520" alt="" class="h-full w-full object-cover">
            </div>
            <div class="absolute bottom-0 left-16 h-52 w-60 -rotate-2 overflow-hidden rounded-2xl border-4 border-white shadow-xl shadow-stone-300/50">
                <img src="https://picsum.photos/seed/eventhub-c/480/420" alt="" class="h-full w-full object-cover">
            </div>
            <span class="absolute -right-2 bottom-10 inline-flex items-center gap-1.5 rounded-full bg-stone-900 px-4 py-2 text-sm font-medium text-white shadow-lg">
                <x-icon name="ticket" class="h-4 w-4 text-emerald-400" /> Live this week
            </span>
        </div>
    </section>

    {{-- Featured events --}}
    @if ($featuredEvents->isNotEmpty())
        <section class="py-12">
            <div class="reveal mb-8 flex items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight text-stone-900 sm:text-3xl">Happening soon</h2>
                    <p class="mt-1 text-stone-600">The next few events on the calendar.</p>
                </div>
                <a href="{{ route('events.index') }}" class="hidden shrink-0 items-center gap-1 text-sm font-medium text-emerald-700 hover:underline sm:inline-flex">
                    View all <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featuredEvents as $event)
                    <x-event-card :event="$event" :index="$loop->index" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Browse by category --}}
    @if ($categories->isNotEmpty())
        <section class="py-12">
            <div class="reveal mb-8">
                <h2 class="text-2xl font-semibold tracking-tight text-stone-900 sm:text-3xl">Browse by category</h2>
                <p class="mt-1 text-stone-600">Jump straight to the kind of event you're after.</p>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($categories as $category)
                    <a href="{{ route('events.index', ['category' => $category->id]) }}"
                       style="--reveal-delay: {{ ($loop->index % 5) * 60 }}ms"
                       class="reveal group flex items-center justify-between gap-2 rounded-xl border border-stone-200 bg-white/80 px-4 py-3.5 transition duration-300 hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md">
                        <span class="flex items-center gap-2.5 font-medium text-stone-800">
                            <span class="grid h-8 w-8 place-items-center rounded-lg bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white">
                                <x-icon name="tag" class="h-4 w-4" />
                            </span>
                            {{ $category->name }}
                        </span>
                        <span class="text-xs text-stone-400">{{ $category->events_count }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- How it works --}}
    <section class="py-12">
        <div class="reveal mb-8">
            <h2 class="text-2xl font-semibold tracking-tight text-stone-900 sm:text-3xl">How it works</h2>
            <p class="mt-1 text-stone-600">From browsing to attending in three simple moves.</p>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            @foreach ([
                ['icon' => 'calendar', 'title' => 'Browse', 'body' => 'Filter by category and find events that fit your schedule.'],
                ['icon' => 'ticket', 'title' => 'Register', 'body' => 'Reserve your spot in a couple of clicks, free or paid.'],
                ['icon' => 'check', 'title' => 'Show up', 'body' => 'Keep everything in My Events and turn up on the day.'],
            ] as $step)
                <div class="reveal rounded-2xl border border-stone-200 bg-white/80 p-6" style="--reveal-delay: {{ $loop->index * 90 }}ms">
                    <span class="grid h-12 w-12 place-items-center rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/25">
                        <x-icon :name="$step['icon']" class="h-6 w-6" />
                    </span>
                    <h3 class="mt-4 text-lg font-semibold text-stone-900">{{ $step['title'] }}</h3>
                    <p class="mt-1.5 text-stone-600">{{ $step['body'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Organizers / admin --}}
    <section class="reveal my-12 overflow-hidden rounded-3xl bg-stone-900 text-white shadow-xl shadow-stone-900/20">
        <div class="grid items-center gap-8 p-8 sm:p-12 lg:grid-cols-2">
            <div>
                <h2 class="font-display text-3xl uppercase leading-tight sm:text-4xl">Organizing an event?</h2>
                <p class="mt-3 max-w-md text-stone-300">
                    Create, publish and manage your events, and see exactly who's coming, all from one dashboard.
                </p>
                <a href="{{ route('admin.login') }}"
                   class="mt-6 inline-flex items-center gap-2 rounded-full bg-emerald-600 px-6 py-3 font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                    Admin sign in <x-icon name="arrow-right" class="h-5 w-5" />
                </a>
            </div>
            <div class="hidden gap-3 sm:grid sm:grid-cols-2">
                <div class="rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                    <x-icon name="calendar" class="h-7 w-7 text-emerald-400" />
                    <p class="mt-3 font-medium">Publish in minutes</p>
                    <p class="mt-1 text-sm text-stone-400">Set the details, pick a category, go live.</p>
                </div>
                <div class="mt-6 rounded-2xl bg-white/5 p-5 ring-1 ring-white/10">
                    <x-icon name="users" class="h-7 w-7 text-emerald-400" />
                    <p class="mt-3 font-medium">Track attendees</p>
                    <p class="mt-1 text-sm text-stone-400">See every registration at a glance.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="reveal py-14 text-center">
        <h2 class="font-display mx-auto max-w-3xl text-4xl uppercase leading-[0.95] tracking-tight text-stone-900 sm:text-5xl">
            Ready to find your next event?
        </h2>
        <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('events.index') }}"
               class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-7 py-3 font-medium text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-700 active:scale-[0.98]">
                Browse events <x-icon name="arrow-right" class="h-5 w-5" />
            </a>
            @guest
                <a href="{{ route('register') }}" class="inline-flex items-center rounded-full border border-stone-300 bg-white/80 px-7 py-3 font-medium text-stone-700 transition hover:border-stone-400">
                    Create account
                </a>
            @endguest
        </div>
    </section>
@endsection
