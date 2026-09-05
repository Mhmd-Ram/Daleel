@extends('layouts.admin')

@section('title', __('app.admin.registrations_title'))

@section('content')
    <a href="{{ route('admin.events.index') }}" class="mb-6 inline-flex text-sm text-stone-500 transition hover:text-stone-900">&larr; {{ __('app.admin.events') }}</a>

    <div class="reveal mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.admin.registered_attendees') }}</h1>
        <p class="mt-1 text-sm text-stone-500">{{ $event->name }} &middot; {{ __('app.admin.registered_count', ['count' => $registrations->total()]) }}</p>
    </div>

    @if ($registrations->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">{{ __('app.admin.no_registrations') }}</p>
            <p class="mt-1 text-stone-500">{{ __('app.admin.no_registrations_body') }}</p>
        </div>
    @else
        <div class="reveal overflow-x-auto rounded-xl border border-stone-200 bg-white" style="--reveal-delay: 80ms">
            <table class="w-full text-start text-sm">
                <thead class="border-b border-stone-200 bg-stone-50 text-stone-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('app.admin.name') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.common.email') }}</th>
                        <th class="px-5 py-3 font-medium whitespace-nowrap">{{ __('app.common.phone') }}</th>
                        <th class="px-5 py-3 font-medium whitespace-nowrap">{{ __('app.common.registered') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($registrations as $user)
                        <tr>
                            <td class="px-5 py-3 font-medium text-stone-900">{{ $user->name }}</td>
                            <td class="px-5 py-3 text-stone-500">{{ $user->email }}</td>
                            <td class="px-5 py-3 text-stone-500 whitespace-nowrap">{{ $user->phone_number }}</td>
                            <td class="px-5 py-3 text-stone-500 whitespace-nowrap">
                                {{ \Illuminate\Support\Carbon::parse($user->pivot->created_at)->format('M j, Y g:i A') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $registrations->links() }}
        </div>
    @endif
@endsection
