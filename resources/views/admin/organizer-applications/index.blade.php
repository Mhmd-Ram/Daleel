@extends('layouts.admin')

@section('title', __('app.admin.applications_title'))

@section('content')
    <div class="reveal mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.admin.applications_title') }}</h1>
        <p class="mt-1 text-sm text-stone-500">{{ __('app.admin.applications_subtitle') }}</p>
    </div>

    <h2 class="mb-4 text-lg font-semibold tracking-tight text-stone-900">{{ __('app.admin.awaiting_review') }}</h2>

    @if ($pending->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">{{ __('app.admin.nothing_to_review') }}</p>
            <p class="mt-1 text-stone-500">{{ __('app.admin.nothing_to_review_body') }}</p>
        </div>
    @else
        <div class="reveal grid gap-4" style="--reveal-delay: 60ms">
            @foreach ($pending as $application)
                <div class="rounded-xl border border-stone-200 bg-white p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="font-medium text-stone-900">{{ $application->user->name }}</p>
                            <p class="text-sm text-stone-500">{{ $application->user->email }}</p>
                            <p class="mt-0.5 text-xs text-stone-400">{{ __('app.admin.applied', ['when' => $application->created_at->diffForHumans()]) }}</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.organizer-applications.approve', $application) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                                    {{ __('app.admin.approve') }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.organizer-applications.reject', $application) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-rose-300 hover:text-rose-700">
                                    {{ __('app.admin.reject') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <p class="mt-4 rounded-lg bg-stone-50 px-4 py-3 text-sm leading-relaxed text-stone-600">
                        {{ $application->message }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif

    @if ($reviewed->isNotEmpty())
        <h2 class="mb-4 mt-10 text-lg font-semibold tracking-tight text-stone-900">{{ __('app.admin.recent_decisions') }}</h2>

        <div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
            <table class="w-full text-start text-sm">
                <thead class="border-b border-stone-200 bg-stone-50 text-stone-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('app.admin.applicant') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.admin.decision') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.admin.reviewed_by') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.admin.when') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($reviewed as $application)
                        <tr>
                            <td class="px-5 py-3">
                                <span class="font-medium text-stone-900">{{ $application->user->name }}</span>
                                <span class="ms-1 text-stone-400">{{ $application->user->email }}</span>
                            </td>
                            <td class="px-5 py-3">
                                @if ($application->status === \App\Enums\OrganizerApplicationStatus::Approved)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">{{ __('app.admin.approved') }}</span>
                                @else
                                    <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-500">{{ __('app.admin.rejected') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-stone-500">{{ $application->reviewer?->name ?? __('app.admin.removed_admin') }}</td>
                            <td class="px-5 py-3 text-stone-500">{{ $application->reviewed_at->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
