@extends('layouts.app')

@section('title', $event->name)

@php
    $pct = $event->max_capacity ? min(100, (int) round($event->registered_users_count / $event->max_capacity * 100)) : null;
@endphp

@section('content')
    <a href="{{ route('events.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm text-stone-500 transition hover:text-stone-900">
        <x-icon name="arrow-right" class="h-4 w-4 rotate-180" /> Back to events
    </a>

    <article class="reveal relative flex flex-col overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-xl shadow-stone-300/30 lg:flex-row">
        {{-- Main: banner + details --}}
        <div class="flex-1">
            <div class="relative aspect-[16/10] w-full overflow-hidden bg-stone-100 sm:aspect-[2/1]">
                <img src="https://picsum.photos/seed/event-{{ $event->id }}/1280/640" alt=""
                     class="h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-stone-950/85 via-stone-950/25 to-transparent"></div>

                <div class="absolute inset-x-0 bottom-0 p-6 sm:p-8">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/95 px-3 py-1 text-xs font-semibold text-emerald-700 shadow-sm">
                        <x-icon name="tag" class="h-3.5 w-3.5" /> {{ $event->category->name }}
                    </span>
                    <h1 class="mt-3 max-w-2xl text-3xl font-bold tracking-tight text-white drop-shadow-sm sm:text-4xl">{{ $event->name }}</h1>
                    <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1.5 text-sm text-white/90">
                        <span class="inline-flex items-center gap-1.5"><x-icon name="calendar" class="h-4 w-4" /> {{ $event->start_date_time->format('D, M j Y') }}</span>
                        <span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="h-4 w-4" /> {{ $event->start_date_time->format('g:i A') }}</span>
                        <span class="inline-flex items-center gap-1.5"><x-icon name="pin" class="h-4 w-4" /> {{ $event->location }}</span>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="flex items-start gap-3 rounded-xl border border-stone-200 bg-stone-50/60 p-4">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-emerald-100 text-emerald-700"><x-icon name="calendar" class="h-5 w-5" /></span>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-stone-400">Starts</p>
                            <p class="mt-0.5 text-sm font-medium text-stone-900">{{ $event->start_date_time->format('M j, Y · g:i A') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 rounded-xl border border-stone-200 bg-stone-50/60 p-4">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-amber-100 text-amber-700"><x-icon name="clock" class="h-5 w-5" /></span>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-stone-400">Ends</p>
                            <p class="mt-0.5 text-sm font-medium text-stone-900">{{ $event->end_date_time->format('M j, Y · g:i A') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 rounded-xl border border-stone-200 bg-stone-50/60 p-4">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-stone-200 text-stone-600"><x-icon name="pin" class="h-5 w-5" /></span>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-stone-400">Location</p>
                            <p class="mt-0.5 text-sm font-medium text-stone-900">{{ $event->location }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 rounded-xl border border-stone-200 bg-stone-50/60 p-4">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-emerald-100 text-emerald-700"><x-icon name="users" class="h-5 w-5" /></span>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-stone-400">Attending</p>
                            <p class="mt-0.5 text-sm font-medium text-stone-900">{{ $event->registered_users_count }}{{ $event->max_capacity ? ' / '.$event->max_capacity : '' }}</p>
                        </div>
                    </div>
                </div>

                @if ($event->hasCoordinates())
                    <div class="mt-8">
                        <h2 class="flex items-center gap-2 text-lg font-semibold text-stone-900">
                            <x-icon name="pin" class="h-5 w-5 text-emerald-600" /> Getting there
                        </h2>
                        <div id="event-location-map" data-map-view
                             data-map-lat="{{ $event->latitude }}"
                             data-map-lng="{{ $event->longitude }}"
                             data-map-label="{{ $event->location }}"
                             class="mt-3 h-64 w-full overflow-hidden rounded-xl border border-stone-200 bg-stone-100 sm:h-80"></div>
                        <p class="mt-2 text-xs text-stone-400">
                            <a href="https://www.openstreetmap.org/?mlat={{ $event->latitude }}&amp;mlon={{ $event->longitude }}#map=16/{{ $event->latitude }}/{{ $event->longitude }}"
                               target="_blank" rel="noopener noreferrer" class="underline hover:text-stone-600">
                                Open in OpenStreetMap
                            </a>
                        </p>
                    </div>

                    @push('scripts')
                        @vite(['resources/js/map.js'])
                    @endpush
                @endif

                <div class="mt-8">
                    <h2 class="flex items-center gap-2 text-lg font-semibold text-stone-900">
                        <x-icon name="sparkles" class="h-5 w-5 text-emerald-600" /> About this event
                    </h2>
                    <p class="mt-3 whitespace-pre-line leading-relaxed text-stone-600">{{ $event->description }}</p>
                </div>
            </div>
        </div>

        {{-- Admission stub --}}
        <aside class="relative shrink-0 border-t-2 border-dashed border-stone-300 bg-stone-50/40 p-6 sm:p-8 lg:w-[340px] lg:border-l-2 lg:border-t-0">
            {{-- Punch holes at the tear-line ends: top corners on mobile, left edge on desktop. --}}
            <span class="ticket-notch left-[-15px] top-[-15px]"></span>
            <span class="ticket-notch right-[-15px] top-[-15px] lg:bottom-[-15px] lg:left-[-15px] lg:right-auto lg:top-auto"></span>

            <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-stone-400">
                <x-icon name="ticket" class="h-4 w-4 text-emerald-600" /> Admission
            </p>
            <p class="mt-3 text-4xl font-bold tracking-tight text-stone-900">
                {{ $event->tiket_cost > 0 ? '$'.number_format($event->tiket_cost, 2) : 'Free' }}
            </p>

            @if ($event->max_capacity)
                <div class="mt-5">
                    <div class="flex items-center justify-between text-xs text-stone-500">
                        <span>{{ $event->registered_users_count }} registered</span>
                        <span>{{ $event->max_capacity }} cap</span>
                    </div>
                    <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-stone-200">
                        <div class="meter__fill h-full rounded-full bg-gradient-to-r from-emerald-500 to-emerald-600" style="--pct: {{ $pct }}%; width: {{ $pct }}%"></div>
                    </div>
                </div>
            @else
                <p class="mt-3 inline-flex items-center gap-1.5 text-sm text-stone-500">
                    <x-icon name="users" class="h-4 w-4 text-stone-400" /> {{ $event->registered_users_count }} attending
                </p>
            @endif

            <div class="mt-6">
                @if ($event->hasFinished())
                    <p class="rounded-xl bg-stone-200 px-4 py-3 text-center text-sm font-medium text-stone-600">This event has ended.</p>
                @elseif (! auth()->check())
                    <a href="{{ route('login') }}"
                       class="flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 font-medium text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-700 hover:shadow-emerald-600/40 active:scale-[0.98]">
                        <x-icon name="ticket" class="h-5 w-5" /> Log in to register
                    </a>
                @elseif ($isRegistered)
                    <p class="mb-3 flex items-center justify-center gap-1.5 rounded-xl bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700">
                        <x-icon name="check" class="h-4 w-4" /> You're registered
                    </p>
                    <form method="POST" action="{{ route('events.cancel', $event) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 font-medium text-stone-700 transition hover:border-rose-300 hover:text-rose-700 active:scale-[0.98]">
                            Cancel registration
                        </button>
                    </form>
                @elseif ($event->isFull())
                    <p class="rounded-xl bg-stone-200 px-4 py-3 text-center text-sm font-medium text-stone-600">This event is full.</p>
                @else
                    <form method="POST" action="{{ route('events.register', $event) }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 font-medium text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-700 hover:shadow-emerald-600/40 active:scale-[0.98]">
                            <x-icon name="ticket" class="h-5 w-5" /> Register for this event
                        </button>
                    </form>
                @endif
            </div>
        </aside>
    </article>
@endsection
