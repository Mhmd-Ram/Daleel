@extends('layouts.app')

@section('title', __('app.organizer.title'))

@section('content')
    <div class="reveal mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.organizer.heading') }}</h1>
            <p class="mt-1 text-sm text-stone-500">{{ __('app.organizer.subtitle') }}</p>
        </div>
        <a href="{{ route('organizer.events.create') }}"
           class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
            {{ __('app.common.new_event') }}
        </a>
    </div>

    @if ($events->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">{{ __('app.common.no_events_yet') }}</p>
            <p class="mt-1 text-stone-500">{{ __('app.organizer.empty_body') }}</p>
        </div>
    @else
        <div class="reveal overflow-hidden rounded-xl border border-stone-200 bg-white" style="--reveal-delay: 80ms">
            <table class="w-full text-start text-sm">
                <thead class="border-b border-stone-200 bg-stone-50 text-stone-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('app.common.event') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.common.category') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.common.starts') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.common.status') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.common.registered') }}</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($events as $event)
                        <tr>
                            <td class="px-5 py-3 font-medium text-stone-900">{{ $event->name }}</td>
                            <td class="px-5 py-3 text-stone-500">{{ $event->category->name }}</td>
                            <td class="px-5 py-3 text-stone-500">{{ $event->start_date_time->format('M j, Y g:i A') }}</td>
                            <td class="px-5 py-3">
                                @if ($event->is_active)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">{{ __('app.common.published') }}</span>
                                @else
                                    <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-500">{{ __('app.common.draft') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-stone-500">{{ $event->registered_users_count }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('events.show', $event) }}"
                                       class="rounded-md border border-stone-300 px-3 py-1.5 text-stone-700 transition hover:border-stone-400">{{ __('app.common.view') }}</a>
                                    <a href="{{ route('organizer.events.edit', $event) }}"
                                       class="rounded-md border border-stone-300 px-3 py-1.5 text-stone-700 transition hover:border-stone-400">{{ __('app.common.edit') }}</a>
                                    <form method="POST" action="{{ route('organizer.events.destroy', $event) }}"
                                          onsubmit="return confirm('{{ __('app.common.confirm_delete_event') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-stone-700 transition hover:border-rose-300 hover:text-rose-700">{{ __('app.common.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
