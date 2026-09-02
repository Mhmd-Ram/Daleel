@extends('layouts.app')

@section('title', 'My events')

@section('content')
    <div class="reveal mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">My events</h1>
        <p class="mt-1 text-sm text-stone-500">Events you've saved to your calendar.</p>
    </div>

    {{-- Month calendar. Renders whether or not the user has registrations, so an
         empty month still reads as a working calendar rather than a broken page. --}}
    <section class="reveal mb-10 overflow-hidden rounded-xl border border-stone-200 bg-white"
             aria-label="Calendar of your saved events">
        <header class="flex items-center justify-between gap-3 border-b border-stone-200 px-4 py-3 sm:px-5">
            <h2 class="text-base font-semibold text-stone-900">
                <time datetime="{{ $month->format('Y-m') }}">{{ $month->format('F Y') }}</time>
            </h2>

            <nav class="flex items-center gap-1.5" aria-label="Change month">
                <a href="{{ route('my-events', ['month' => $previousMonth]) }}" rel="prev"
                   aria-label="Previous month, {{ $month->subMonth()->format('F Y') }}"
                   class="grid h-9 w-9 place-items-center rounded-lg border border-stone-300 text-stone-600 transition hover:border-stone-400 hover:text-stone-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">
                    <x-icon name="chevron-left" class="h-4 w-4" />
                </a>

                <a href="{{ route('my-events') }}"
                   class="rounded-lg border border-stone-300 px-3 py-2 text-sm font-medium text-stone-700 transition hover:border-stone-400 hover:text-stone-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">
                    Today
                </a>

                <a href="{{ route('my-events', ['month' => $nextMonth]) }}" rel="next"
                   aria-label="Next month, {{ $month->addMonth()->format('F Y') }}"
                   class="grid h-9 w-9 place-items-center rounded-lg border border-stone-300 text-stone-600 transition hover:border-stone-400 hover:text-stone-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">
                    <x-icon name="chevron-right" class="h-4 w-4" />
                </a>
            </nav>
        </header>

        <div class="grid grid-cols-7 border-b border-stone-200 bg-stone-50">
            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday)
                <div class="px-1 py-2 text-center text-[10px] font-semibold uppercase tracking-wide text-stone-500 sm:text-xs">
                    {{ $weekday }}
                </div>
            @endforeach
        </div>

        @foreach ($weeks as $week)
            <div class="grid grid-cols-7 border-b border-stone-100 last:border-b-0">
                @foreach ($week as $day)
                    <div @class([
                        'min-h-[4.75rem] border-r border-stone-100 p-1 last:border-r-0 sm:min-h-[6.5rem] sm:p-1.5',
                        'bg-stone-50/70' => ! $day['inMonth'],
                    ])>
                        <time datetime="{{ $day['date']->toDateString() }}"
                              @class([
                                  'inline-grid h-6 w-6 place-items-center rounded-full text-[11px] sm:text-xs',
                                  'text-stone-300' => ! $day['inMonth'],
                                  'font-medium text-stone-700' => $day['inMonth'] && ! $day['isToday'],
                                  'bg-emerald-700 font-semibold text-white' => $day['isToday'],
                              ])>{{ $day['date']->day }}</time>

                        @if ($day['events']->isNotEmpty())
                            <ul class="mt-0.5 space-y-0.5">
                                @foreach ($day['events']->take($eventsPerDayCell) as $dayEvent)
                                    <li>
                                        <a href="{{ route('events.show', $dayEvent) }}"
                                           title="{{ $dayEvent->name }} ({{ $dayEvent->start_date_time->format('g:i A') }})"
                                           class="block truncate rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-medium text-emerald-800 transition hover:bg-emerald-100 sm:text-[11px]">
                                            {{ $dayEvent->name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            @if ($day['events']->count() > $eventsPerDayCell)
                                <p class="mt-0.5 px-1.5 text-[10px] text-stone-500">
                                    +{{ $day['events']->count() - $eventsPerDayCell }} more
                                </p>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </section>

    @if ($events->isEmpty())
        <div class="reveal rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">You haven't saved anything yet</p>
            <p class="mt-1 text-stone-500">Browse events and save them to see them here.</p>
            <a href="{{ route('events.index') }}" class="mt-5 inline-flex rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700">Browse events</a>
        </div>
    @else
        <h2 class="reveal mb-4 text-base font-semibold text-stone-900">All registrations</h2>

        <div class="space-y-4">
            @foreach ($events as $event)
                <div style="--reveal-delay: {{ min($loop->index, 6) * 70 }}ms"
                     class="reveal reveal--left flex flex-col gap-4 rounded-xl border border-stone-200 bg-white p-4 transition duration-300 hover:border-stone-300 hover:shadow-md sm:flex-row sm:items-center">
                    <img src="https://picsum.photos/seed/event-{{ $event->id }}/200/200" alt=""
                         class="h-24 w-full rounded-lg object-cover sm:w-24" loading="lazy">
                    <div class="flex-1">
                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">{{ $event->category->name }}</span>
                        <h3 class="mt-2 text-lg font-semibold text-stone-900">{{ $event->name }}</h3>
                        <p class="text-sm text-stone-500">{{ $event->start_date_time->format('D, M j Y · g:i A') }} &middot; {{ $event->location }}</p>
                        @if ($event->hasFinished())
                            <span class="mt-1 inline-flex text-xs font-medium text-stone-400">This event has ended</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('events.show', $event) }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-stone-400">Details</a>
                        <form method="POST" action="{{ route('events.cancel', $event) }}"
                              onsubmit="return confirm('Remove this event from your calendar?')">
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
