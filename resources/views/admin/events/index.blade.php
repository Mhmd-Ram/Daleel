@extends('layouts.admin')

@section('title', __('app.admin.events'))

@section('content')
    <div class="reveal mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.admin.events') }}</h1>
            <p class="mt-1 text-sm text-stone-500">{{ __('app.admin.events_subtitle') }}</p>
        </div>
        <a href="{{ route('admin.events.create') }}"
           class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
            {{ __('app.common.new_event') }}
        </a>
    </div>

    {{-- Filters (FR-12.2). GET, so a filtered view stays linkable. --}}
    <form method="GET" action="{{ route('admin.events.index') }}"
          class="reveal mb-6 rounded-xl border border-stone-200 bg-white p-4" style="--reveal-delay: 40ms">
        <div class="grid gap-3 md:grid-cols-[auto_auto_auto_minmax(0,1fr)]">
            <div>
                <label for="owner" class="sr-only">{{ __('app.admin.filter_by_creator') }}</label>
                <select id="owner" name="owner"
                        class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                    <option value="" @selected($selectedOwner === null)>{{ __('app.admin.all_creators') }}</option>
                    <option value="admin" @selected($selectedOwner === 'admin')>{{ __('app.admin.admin_created') }}</option>
                    <option value="organizer" @selected($selectedOwner === 'organizer')>{{ __('app.admin.organizer_created') }}</option>
                </select>
            </div>

            <div>
                <label for="status" class="sr-only">{{ __('app.admin.filter_by_status') }}</label>
                <select id="status" name="status"
                        class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                    <option value="" @selected($selectedStatus === null)>{{ __('app.admin.all_status') }}</option>
                    <option value="published" @selected($selectedStatus === 'published')>{{ __('app.common.published') }}</option>
                    <option value="draft" @selected($selectedStatus === 'draft')>{{ __('app.common.draft') }}</option>
                </select>
            </div>

            <button type="submit"
                    class="rounded-lg border border-emerald-600 bg-emerald-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 active:translate-y-px">
                {{ __('app.admin.filter') }}
            </button>
        </div>
    </form>

    @if ($events->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">{{ __('app.common.no_events_yet') }}</p>
            <p class="mt-1 text-stone-500">{{ __('app.admin.events_empty_body') }}</p>
        </div>
    @else
        <div class="reveal overflow-x-auto rounded-xl border border-stone-200 bg-white" style="--reveal-delay: 80ms">
            <table class="w-full text-start text-sm">
                <thead class="border-b border-stone-200 bg-stone-50 text-stone-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('app.common.event') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.admin.owner') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.common.category') }}</th>
                        <th class="px-5 py-3 font-medium whitespace-nowrap">{{ __('app.common.starts') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.common.status') }}</th>
                        <th class="px-5 py-3 text-end font-medium whitespace-nowrap">{{ __('app.common.registered') }}</th>
                        <th class="px-5 py-3 text-end font-medium">{{ __('app.admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($events as $event)
                        <tr>
                            <td class="px-5 py-3 font-medium text-stone-900">{{ $event->name }}</td>
                            <td class="px-5 py-3">
                                <span class="text-stone-900">{{ $event->owner()->name }}</span>
                                <span class="mt-0.5 inline-flex rounded-full bg-stone-100 px-2 py-0.5 text-xs font-medium text-stone-500">
                                    {{ $event->admin_id !== null ? __('app.admin.label') : __('app.admin.organizer') }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-stone-500">{{ $event->category->name }}</td>
                            <td class="px-5 py-3 text-stone-500 whitespace-nowrap">{{ $event->start_date_time->format('M j, Y g:i A') }}</td>
                            <td class="px-5 py-3">
                                @if ($event->is_active)
                                    <span class="inline-flex rounded-full bg-ok-50 px-2.5 py-0.5 text-xs font-medium text-ok-700">{{ __('app.admin.active') }}</span>
                                @else
                                    <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-500">{{ __('app.admin.inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-end tabular-nums">
                                <a href="{{ route('admin.events.registrations', $event) }}" class="font-medium text-emerald-700 hover:underline">
                                    {{ $event->registered_users_count }}
                                </a>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($event->reports_count > 0)
                                        <a href="{{ route('admin.reports.index', ['event' => $event->id]) }}"
                                           class="rounded-full bg-bad-50 px-2.5 py-0.5 text-xs font-medium whitespace-nowrap text-bad-700 transition hover:bg-bad-200/60">
                                            {{ trans_choice('app.admin.reports_badge', $event->reports_count, ['count' => $event->reports_count]) }}
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('admin.events.publish', $event) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-stone-700 transition hover:border-stone-400">
                                            {{ $event->is_active ? __('app.admin.unpublish') : __('app.admin.publish') }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.events.edit', $event) }}"
                                       class="rounded-md border border-stone-300 px-3 py-1.5 text-stone-700 transition hover:border-stone-400">{{ __('app.common.edit') }}</a>
                                    <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
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

        <div class="mt-8">
            {{ $events->links() }}
        </div>
    @endif
@endsection
