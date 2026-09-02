@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="reveal mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Dashboard</h1>
        <p class="mt-1 text-sm text-stone-500">How {{ config('app.name') }} is doing right now.</p>
    </div>

    <div class="reveal grid gap-4 sm:grid-cols-2 lg:grid-cols-4" style="--reveal-delay: 60ms">
        @foreach ([
            ['label' => 'Events', 'value' => $stats['events'], 'note' => $stats['published'].' published, '.$stats['drafts'].' draft'],
            ['label' => 'Upcoming', 'value' => $stats['upcoming'], 'note' => 'Published and not yet finished'],
            ['label' => 'Registrations', 'value' => $stats['registrations'], 'note' => 'Across every event'],
            ['label' => 'Users', 'value' => $stats['users'], 'note' => $stats['organizers'].' organizers, '.$stats['bannedUsers'].' banned', 'href' => route('admin.users.index')],
        ] as $card)
            <div class="rounded-xl border border-stone-200 bg-white p-5">
                <p class="text-xs uppercase tracking-wide text-stone-400">{{ $card['label'] }}</p>
                @isset($card['href'])
                    <a href="{{ $card['href'] }}" class="mt-2 block text-3xl font-bold text-stone-900 transition hover:text-emerald-700">{{ number_format($card['value']) }}</a>
                @else
                    <p class="mt-2 text-3xl font-bold text-stone-900">{{ number_format($card['value']) }}</p>
                @endisset
                <p class="mt-1 text-xs text-stone-500">{{ $card['note'] }}</p>
            </div>
        @endforeach
    </div>

    @if ($stats['pendingApplications'] > 0)
        <div class="reveal mt-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4"
             style="--reveal-delay: 100ms">
            <p class="text-sm text-emerald-800">
                <span class="font-semibold">{{ $stats['pendingApplications'] }}</span>
                organizer {{ Str::plural('application', $stats['pendingApplications']) }} waiting for review.
            </p>
            <a href="{{ route('admin.organizer-applications.index') }}"
               class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                Review now
            </a>
        </div>
    @endif

    {{-- A banner rather than a fifth stat card: reports are something to act
         on, and the card row is for standing numbers. --}}
    @if ($stats['reports'] > 0)
        <div class="reveal mt-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4"
             style="--reveal-delay: 100ms">
            <p class="text-sm text-rose-800">
                <span class="font-semibold">{{ $stats['reports'] }}</span>
                {{ Str::plural('report', $stats['reports']) }} waiting for review.
            </p>
            <a href="{{ route('admin.reports.index') }}"
               class="rounded-lg bg-rose-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-800 active:scale-[0.98]">
                Open reports
            </a>
        </div>
    @endif

    <div class="reveal mt-10" style="--reveal-delay: 140ms">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold tracking-tight text-stone-900">Latest events</h2>
            <a href="{{ route('admin.events.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">All events</a>
        </div>

        @if ($recentEvents->isEmpty())
            <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
                <p class="text-lg font-medium text-stone-900">Nothing has been created yet</p>
                <p class="mt-1 text-stone-500">New events from you and from organizers will show up here.</p>
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
                <table class="w-full text-start text-sm">
                    <thead class="border-b border-stone-200 bg-stone-50 text-stone-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Event</th>
                            <th class="px-5 py-3 font-medium">Created by</th>
                            <th class="px-5 py-3 font-medium">Starts</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Registered</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($recentEvents as $event)
                            <tr>
                                <td class="px-5 py-3 font-medium text-stone-900">{{ $event->name }}</td>
                                <td class="px-5 py-3 text-stone-500">
                                    {{ $event->owner()->name }}
                                    <span class="ms-1 text-xs text-stone-400">{{ $event->admin_id ? 'admin' : 'organizer' }}</span>
                                </td>
                                <td class="px-5 py-3 text-stone-500">{{ $event->start_date_time->format('M j, Y') }}</td>
                                <td class="px-5 py-3">
                                    @if ($event->is_active)
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Published</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-500">Draft</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-stone-500">{{ $event->registered_users_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
