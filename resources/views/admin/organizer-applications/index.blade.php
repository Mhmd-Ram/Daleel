@extends('layouts.admin')

@section('title', 'Organizer applications')

@section('content')
    <div class="reveal mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Organizer applications</h1>
        <p class="mt-1 text-sm text-stone-500">Approve a user to let them create and manage their own events.</p>
    </div>

    <h2 class="mb-4 text-lg font-semibold tracking-tight text-stone-900">Awaiting review</h2>

    @if ($pending->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">Nothing to review</p>
            <p class="mt-1 text-stone-500">New applications will appear here as users send them.</p>
        </div>
    @else
        <div class="reveal grid gap-4" style="--reveal-delay: 60ms">
            @foreach ($pending as $application)
                <div class="rounded-xl border border-stone-200 bg-white p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="font-medium text-stone-900">{{ $application->user->name }}</p>
                            <p class="text-sm text-stone-500">{{ $application->user->email }}</p>
                            <p class="mt-0.5 text-xs text-stone-400">Applied {{ $application->created_at->diffForHumans() }}</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.organizer-applications.approve', $application) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                                    Approve
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.organizer-applications.reject', $application) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-rose-300 hover:text-rose-700">
                                    Reject
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
        <h2 class="mb-4 mt-10 text-lg font-semibold tracking-tight text-stone-900">Recent decisions</h2>

        <div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-stone-200 bg-stone-50 text-stone-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Applicant</th>
                        <th class="px-5 py-3 font-medium">Decision</th>
                        <th class="px-5 py-3 font-medium">Reviewed by</th>
                        <th class="px-5 py-3 font-medium">When</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($reviewed as $application)
                        <tr>
                            <td class="px-5 py-3">
                                <span class="font-medium text-stone-900">{{ $application->user->name }}</span>
                                <span class="ml-1 text-stone-400">{{ $application->user->email }}</span>
                            </td>
                            <td class="px-5 py-3">
                                @if ($application->status === \App\Enums\OrganizerApplicationStatus::Approved)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Approved</span>
                                @else
                                    <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-500">Rejected</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-stone-500">{{ $application->reviewer?->name ?? 'Removed admin' }}</td>
                            <td class="px-5 py-3 text-stone-500">{{ $application->reviewed_at->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
