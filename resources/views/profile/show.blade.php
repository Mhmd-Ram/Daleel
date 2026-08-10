@extends('layouts.app')

@section('title', 'Profile')

@php
    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

@section('content')
    <div class="reveal mx-auto max-w-xl">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Your pass</h1>
            <a href="{{ route('profile.edit') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-stone-300 bg-white/80 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-stone-400 hover:shadow-sm">
                Edit
            </a>
        </div>

        <div class="pass relative overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-xl shadow-stone-300/30">
            <span class="pass-shine"></span>

            {{-- Cover: tag pinned to the top so it never collides with the
                 avatar that overlaps from the bottom edge. --}}
            <div class="relative h-36 overflow-hidden bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-600">
                <x-icon name="ticket" class="absolute -bottom-5 -right-4 h-28 w-28 rotate-12 text-white/15" />
                <p class="absolute left-6 top-5 inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-[0.22em] text-white/85">
                    <x-icon name="ticket" class="h-4 w-4" /> Attendee pass
                </p>
                <x-icon name="sparkles" class="absolute right-6 top-5 h-6 w-6 text-white/35" />
            </div>

            <div class="px-6 pb-6 sm:px-8">
                {{-- Identity: only the avatar overlaps the cover; name sits below it. --}}
                <div class="relative z-10 -mt-10 grid h-20 w-20 place-items-center rounded-2xl border-4 border-white bg-stone-900 text-2xl font-bold text-white shadow-lg">
                    {{ $initials }}
                </div>
                <div class="mt-3">
                    <h2 class="text-xl font-bold text-stone-900">{{ $user->name }}</h2>
                    <p class="mt-0.5 flex items-center gap-1.5 text-sm text-stone-500"><x-icon name="pin" class="h-4 w-4 text-stone-400" /> {{ $user->location }}</p>
                </div>

                {{-- Stats --}}
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-emerald-50 p-4">
                        <p class="text-2xl font-bold text-emerald-700">{{ $user->registrations_count }}</p>
                        <p class="text-xs uppercase tracking-wide text-emerald-600/70">Events joined</p>
                    </div>
                    <div class="rounded-xl bg-stone-100 p-4">
                        <p class="text-2xl font-bold text-stone-900">{{ $user->created_at->format('M Y') }}</p>
                        <p class="text-xs uppercase tracking-wide text-stone-400">Member since</p>
                    </div>
                </div>

                {{-- Details --}}
                <dl class="mt-6 divide-y divide-stone-100 overflow-hidden rounded-xl border border-stone-200">
                    <div class="flex items-center gap-3 px-4 py-3.5">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-stone-100 text-stone-500"><x-icon name="user" class="h-4 w-4" /></span>
                        <dt class="w-20 text-xs uppercase tracking-wide text-stone-400">Email</dt>
                        <dd class="text-sm font-medium text-stone-900">{{ $user->email }}</dd>
                    </div>
                    <div class="flex items-center gap-3 px-4 py-3.5">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-stone-100 text-stone-500"><x-icon name="ticket" class="h-4 w-4" /></span>
                        <dt class="w-20 text-xs uppercase tracking-wide text-stone-400">Phone</dt>
                        <dd class="text-sm font-medium text-stone-900">{{ $user->phone_number }}</dd>
                    </div>
                    <div class="flex items-center gap-3 px-4 py-3.5">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-stone-100 text-stone-500"><x-icon name="calendar" class="h-4 w-4" /></span>
                        <dt class="w-20 text-xs uppercase tracking-wide text-stone-400">Born</dt>
                        <dd class="text-sm font-medium text-stone-900">{{ $user->dob->format('M j, Y') }}</dd>
                    </div>
                </dl>

                {{-- Barcode --}}
                <div class="mt-6 border-t border-dashed border-stone-300 pt-5">
                    <div class="barcode h-12 w-full rounded"></div>
                    <p class="mt-2 text-center font-mono text-[10px] uppercase tracking-[0.25em] text-stone-400">PASS · {{ str_pad($user->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
