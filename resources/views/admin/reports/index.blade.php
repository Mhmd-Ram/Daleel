@extends('layouts.admin')

@section('title', __('app.admin.reports'))

@section('content')
    <div class="reveal mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.admin.reports') }}</h1>
        <p class="mt-1 text-sm text-stone-500">{{ __('app.admin.reports_subtitle') }}</p>
    </div>

    @if ($mostReported->isNotEmpty())
        <div class="reveal mb-8 rounded-xl border border-stone-200 bg-white p-5" style="--reveal-delay: 40ms">
            <h2 class="text-sm font-medium text-stone-900">{{ __('app.admin.most_reported') }}</h2>
            <ul class="mt-3 divide-y divide-stone-100">
                @foreach ($mostReported as $event)
                    <li class="flex items-center justify-between gap-4 py-2">
                        <a href="{{ route('admin.events.edit', $event) }}" class="text-sm text-stone-700 hover:underline">
                            {{ $event->name }}
                        </a>
                        <span class="inline-flex shrink-0 rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-700">
                            {{ trans_choice('app.admin.reports_badge', $event->reports_count, ['count' => $event->reports_count]) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($reports->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">{{ __('app.admin.nothing_reported') }}</p>
            <p class="mt-1 text-stone-500">{{ __('app.admin.nothing_reported_body') }}</p>
        </div>
    @else
        <div class="reveal overflow-hidden rounded-xl border border-stone-200 bg-white" style="--reveal-delay: 80ms">
            <table class="w-full text-start text-sm">
                <thead class="border-b border-stone-200 bg-stone-50 text-stone-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('app.common.event') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.admin.reported_by') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.admin.reason') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.admin.when') }}</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($reports as $report)
                        <tr>
                            <td class="px-5 py-3">
                                <span class="font-medium text-stone-900">{{ $report->event->name }}</span>
                                <span class="block text-xs text-stone-400">{{ $report->event->owner()->name }}</span>
                            </td>
                            <td class="px-5 py-3 text-stone-500">{{ $report->attendee->name }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-600">
                                    {{ $report->reason->label() }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-stone-500">{{ $report->created_at->format('M j, Y') }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.events.edit', $report->event) }}"
                                       class="rounded-md border border-stone-300 px-3 py-1.5 text-stone-700 transition hover:border-stone-400">{{ __('app.admin.review_event') }}</a>
                                    <form method="POST" action="{{ route('admin.reports.destroy', $report) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-stone-700 transition hover:border-rose-300 hover:text-rose-700">{{ __('app.admin.dismiss') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $reports->links() }}
        </div>
    @endif
@endsection
