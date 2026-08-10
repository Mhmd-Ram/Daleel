@extends('layouts.app')

@section('title', 'Browse events')

@section('content')
    <section class="reveal mb-10">
        <h1 class="text-3xl font-semibold tracking-tight text-stone-900 sm:text-4xl">Find your next event</h1>
        <p class="mt-2 max-w-prose text-stone-600">Browse what's coming up and register in a couple of clicks.</p>
    </section>

    <div class="reveal mb-8 flex flex-wrap gap-2" style="--reveal-delay: 80ms">
        <a href="{{ route('events.index') }}"
           class="rounded-full border px-4 py-1.5 text-sm transition duration-300 hover:-translate-y-0.5 {{ ! $selectedCategory ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-stone-300 bg-white text-stone-600 hover:border-stone-400' }}">
            All
        </a>
        @foreach ($categories as $category)
            <a href="{{ route('events.index', ['category' => $category->id]) }}"
               class="rounded-full border px-4 py-1.5 text-sm transition duration-300 hover:-translate-y-0.5 {{ $selectedCategory === $category->id ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-stone-300 bg-white text-stone-600 hover:border-stone-400' }}">
                {{ $category->name }}
            </a>
        @endforeach
    </div>

    @if ($events->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">No events here yet</p>
            <p class="mt-1 text-stone-500">There are no published events in this view right now. Check back soon.</p>
        </div>
    @else
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
