@use('Illuminate\Support\Str')

@extends('layouts.app')

@section('title', 'Browse events')

@section('content')
    <section class="reveal mb-8">
        <h1 class="text-3xl font-semibold tracking-tight text-stone-900 sm:text-4xl">Find your next event</h1>
        <p class="mt-2 max-w-prose text-stone-600">Browse what's coming up and register in a couple of clicks.</p>
    </section>

    {{-- Search and filters (FR-4.2 - FR-4.5). A plain GET form, so the query string
         is the whole state: every filtered view is linkable and the back button works. --}}
    <form method="GET" action="{{ route('events.index') }}"
          class="reveal mb-6 rounded-xl border border-stone-200 bg-white p-4" style="--reveal-delay: 40ms">
        {{-- Carries the chip selection through a search so the chips and this form
             do not overwrite each other. --}}
        <input type="hidden" name="category" value="{{ $selectedCategory }}">

        <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_auto_auto_auto]">
            <div>
                <label for="q" class="sr-only">Search events</label>
                <input id="q" name="q" type="search" value="{{ $keyword }}"
                       placeholder="Search for workshops, conferences..."
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 text-stone-900 placeholder:text-stone-500 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div>
                <label for="city" class="sr-only">Filter by city</label>
                <select id="city" name="city"
                        class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">All cities</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->value }}" @selected($selectedCity === $city)>{{ $city->value }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="date" class="sr-only">Filter by date</label>
                <select id="date" name="date"
                        class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                    <option value="" @selected($selectedDate === '')>All dates</option>
                    <option value="today" @selected($selectedDate === 'today')>Today</option>
                    <option value="this_week" @selected($selectedDate === 'this_week')>This week</option>
                    <option value="this_month" @selected($selectedDate === 'this_month')>This month</option>
                    <option value="next_month" @selected($selectedDate === 'next_month')>Next month</option>
                </select>
            </div>

            <button type="submit"
                    class="rounded-lg border border-emerald-600 bg-emerald-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 active:translate-y-px">
                Search
            </button>
        </div>
    </form>

    @php
        // The filters the category chips must carry, so switching category never
        // silently discards the keyword, city or date the reader already chose.
        $carried = array_filter([
            'q' => $keyword,
            'city' => $selectedCity?->value,
            'date' => $selectedDate ?: null,
        ]);
    @endphp

    <div class="reveal mb-8 flex flex-wrap gap-2" style="--reveal-delay: 80ms">
        <a href="{{ route('events.index', $carried) }}"
           class="rounded-full border px-4 py-1.5 text-sm transition duration-300 hover:-translate-y-0.5 {{ ! $selectedCategory ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-stone-300 bg-white text-stone-600 hover:border-stone-400' }}">
            All
        </a>
        @foreach ($categories as $category)
            <a href="{{ route('events.index', array_merge($carried, ['category' => $category->id])) }}"
               class="rounded-full border px-4 py-1.5 text-sm transition duration-300 hover:-translate-y-0.5 {{ $selectedCategory === $category->id ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-stone-300 bg-white text-stone-600 hover:border-stone-400' }}">
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    @if ($events->isEmpty() && $isFiltered)
        {{-- FR-4.4: "nothing matched" is a different message from "nothing published". --}}
        <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">No results found</p>
            <p class="mt-1 text-stone-500">Nothing matches those filters. Try a different keyword, or clear the filters.</p>
            <a href="{{ route('events.index') }}"
               class="mt-5 inline-block rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-stone-400 hover:text-stone-900">
                Clear filters
            </a>
        </div>
    @elseif ($events->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">No events here yet</p>
            <p class="mt-1 text-stone-500">There are no published events in this view right now. Check back soon.</p>
        </div>
    @else
        @if ($isFiltered)
            <p class="mb-4 text-sm text-stone-500">
                {{ $events->total() }} {{ Str::plural('event', $events->total()) }} found
            </p>
        @endif

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($events as $event)
                <x-event-card :event="$event" :index="$loop->index" />
            @endforeach
        </div>

        <div class="mt-10">
            {{ $events->links() }}
        </div>
    @endif
@endsection
