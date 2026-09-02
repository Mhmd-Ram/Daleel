@extends('layouts.admin')

@section('title', __('app.admin.dashboard'))

@section('content')
    <div class="reveal mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.admin.dashboard') }}</h1>
        <p class="mt-1 text-sm text-stone-500">How {{ config('app.name') }} is doing right now.</p>
    </div>

    <div class="reveal grid gap-4 sm:grid-cols-2 lg:grid-cols-4" style="--reveal-delay: 60ms">
        @foreach ([
            ['label' => __('app.admin.card_events'), 'value' => $stats['events'], 'note' => __('app.admin.card_events_note', ['published' => $stats['published'], 'drafts' => $stats['drafts']])],
            ['label' => __('app.admin.card_upcoming'), 'value' => $stats['upcoming'], 'note' => __('app.admin.card_upcoming_note')],
            ['label' => __('app.admin.card_registrations'), 'value' => $stats['registrations'], 'note' => __('app.admin.card_registrations_note')],
            ['label' => __('app.admin.card_users'), 'value' => $stats['users'], 'note' => __('app.admin.card_users_note', ['organizers' => $stats['organizers'], 'banned' => $stats['bannedUsers']]), 'href' => route('admin.users.index')],
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
                {{ trans_choice('app.admin.applications_waiting', $stats['pendingApplications'], ['count' => $stats['pendingApplications']]) }}
            </p>
            <a href="{{ route('admin.organizer-applications.index') }}"
               class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                {{ __('app.admin.review_now') }}
            </a>
        </div>
    @endif

    {{-- A banner rather than a fifth stat card: reports are something to act
         on, and the card row is for standing numbers. --}}
    @if ($stats['reports'] > 0)
        <div class="reveal mt-6 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4"
             style="--reveal-delay: 100ms">
            <p class="text-sm text-rose-800">
                {{ trans_choice('app.admin.reports_waiting', $stats['reports'], ['count' => $stats['reports']]) }}
            </p>
            <a href="{{ route('admin.reports.index') }}"
               class="rounded-lg bg-rose-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-rose-800 active:scale-[0.98]">
                {{ __('app.admin.open_reports') }}
            </a>
        </div>
    @endif

    <div class="reveal mt-10" style="--reveal-delay: 140ms">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold tracking-tight text-stone-900">{{ __('app.admin.latest_events') }}</h2>
            <a href="{{ route('admin.events.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">{{ __('app.admin.all_events') }}</a>
        </div>

        @if ($recentEvents->isEmpty())
            <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
                <p class="text-lg font-medium text-stone-900">{{ __('app.admin.nothing_created') }}</p>
                <p class="mt-1 text-stone-500">{{ __('app.admin.nothing_created_body') }}</p>
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
                <table class="w-full text-start text-sm">
                    <thead class="border-b border-stone-200 bg-stone-50 text-stone-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">{{ __('app.common.event') }}</th>
                            <th class="px-5 py-3 font-medium">{{ __('app.admin.created_by') }}</th>
                            <th class="px-5 py-3 font-medium">{{ __('app.common.starts') }}</th>
                            <th class="px-5 py-3 font-medium">{{ __('app.common.status') }}</th>
                            <th class="px-5 py-3 font-medium">{{ __('app.common.registered') }}</th>
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
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">{{ __('app.common.published') }}</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-500">{{ __('app.common.draft') }}</span>
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
