@extends('layouts.app')

@section('title', 'My events')

@section('content')
    <div class="reveal mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">My events</h1>
        <p class="mt-1 text-sm text-stone-500">Events you've registered to attend.</p>
    </div>

    @if ($events->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">You haven't registered for anything yet</p>
            <p class="mt-1 text-stone-500">Browse events and register to see them here.</p>
            <a href="{{ route('events.index') }}" class="mt-5 inline-flex rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">Browse events</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($events as $event)
                <div style="--reveal-delay: {{ min($loop->index, 6) * 70 }}ms"
                     class="reveal reveal--left flex flex-col gap-4 rounded-xl border border-stone-200 bg-white p-4 transition duration-300 hover:border-stone-300 hover:shadow-md sm:flex-row sm:items-center">
                    <img src="https://picsum.photos/seed/event-{{ $event->id }}/200/200" alt=""
                         class="h-24 w-full rounded-lg object-cover sm:w-24" loading="lazy">
                    <div class="flex-1">
                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">{{ $event->category->name }}</span>
                        <h2 class="mt-2 text-lg font-semibold text-stone-900">{{ $event->name }}</h2>
                        <p class="text-sm text-stone-500">{{ $event->start_date_time->format('D, M j Y · g:i A') }} &middot; {{ $event->location }}</p>
                        @if ($event->hasFinished())
                            <span class="mt-1 inline-flex text-xs font-medium text-stone-400">This event has ended</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('events.show', $event) }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-stone-400">Details</a>
                        <form method="POST" action="{{ route('events.cancel', $event) }}"
                              onsubmit="return confirm('Cancel your registration for this event?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-rose-300 hover:text-rose-700">Cancel</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
