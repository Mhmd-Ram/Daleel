@use('App\Models\OrganizerApplication')

@extends('layouts.app')

@section('title', __('app.profile.title'))

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
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.profile.your_pass') }}</h1>
            <a href="{{ route('profile.edit') }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-stone-300 bg-white/80 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-stone-400 hover:shadow-sm">
                {{ __('app.common.edit') }}
            </a>
        </div>

        <div class="pass relative overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-xl shadow-stone-300/30">
            <span class="pass-shine"></span>

            {{-- Cover: tag pinned to the top so it never collides with the
                 avatar that overlaps from the bottom edge. --}}
            <div class="relative h-36 overflow-hidden bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-600">
                <x-icon name="ticket" class="absolute -bottom-5 -right-4 h-28 w-28 rotate-12 text-white/15" />
                <p class="absolute start-6 top-5 inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-[0.22em] text-white/85">
                    <x-icon name="ticket" class="h-4 w-4" /> {{ __('app.profile.attendee_pass') }}
                </p>
                <x-icon name="sparkles" class="absolute end-6 top-5 h-6 w-6 text-white/35" />
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
                        <p class="text-xs uppercase tracking-wide text-emerald-600/70">{{ __('app.profile.events_joined') }}</p>
                    </div>
                    <div class="rounded-xl bg-stone-100 p-4">
                        <p class="text-2xl font-bold text-stone-900">{{ $user->created_at->format('M Y') }}</p>
                        <p class="text-xs uppercase tracking-wide text-stone-400">{{ __('app.profile.member_since') }}</p>
                    </div>
                </div>

                {{-- Details --}}
                <dl class="mt-6 divide-y divide-stone-100 overflow-hidden rounded-xl border border-stone-200">
                    <div class="flex items-center gap-3 px-4 py-3.5">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-stone-100 text-stone-500"><x-icon name="user" class="h-4 w-4" /></span>
                        <dt class="w-20 text-xs uppercase tracking-wide text-stone-400">{{ __('app.common.email') }}</dt>
                        <dd class="text-sm font-medium text-stone-900">{{ $user->email }}</dd>
                    </div>
                    <div class="flex items-center gap-3 px-4 py-3.5">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-stone-100 text-stone-500"><x-icon name="ticket" class="h-4 w-4" /></span>
                        <dt class="w-20 text-xs uppercase tracking-wide text-stone-400">{{ __('app.common.phone') }}</dt>
                        <dd class="text-sm font-medium text-stone-900">{{ $user->phone_number }}</dd>
                    </div>
                    <div class="flex items-center gap-3 px-4 py-3.5">
                        <span class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-stone-100 text-stone-500"><x-icon name="calendar" class="h-4 w-4" /></span>
                        <dt class="w-20 text-xs uppercase tracking-wide text-stone-400">{{ __('app.profile.born') }}</dt>
                        <dd class="text-sm font-medium text-stone-900">{{ $user->dob->format('M j, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Organizer status: promoted, waiting on a decision, or free to apply. --}}
        <div class="mt-6 rounded-2xl border border-stone-200 bg-white p-6">
            @if ($user->isOrganizer())
                <h2 class="text-lg font-semibold tracking-tight text-stone-900">{{ __('app.profile.you_are_organizer') }}</h2>
                <p class="mt-1 text-sm text-stone-500">{{ __('app.profile.organizer_sub') }}</p>
                <a href="{{ route('organizer.events.index') }}"
                   class="mt-4 inline-flex rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                    {{ __('app.profile.manage_your_events') }}
                </a>
            @elseif ($organizerApplication?->isPending())
                <h2 class="text-lg font-semibold tracking-tight text-stone-900">{{ __('app.profile.under_review') }}</h2>
                <p class="mt-1 text-sm text-stone-500">
                    {{ __('app.profile.application_sent_ago', ['ago' => $organizerApplication->created_at->diffForHumans()]) }}
                </p>
            @elseif (! $user->hasVerifiedEmail())
                <h2 class="text-lg font-semibold tracking-tight text-stone-900">{{ __('app.profile.want_to_run') }}</h2>
                <p class="mt-1 text-sm text-stone-500">
                    {{ __('app.profile.verify_first') }}
                </p>
                <a href="{{ route('verification.notice') }}"
                   class="mt-4 inline-flex rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                    {{ __('app.profile.verify_your_email') }}
                </a>
            @else
                <h2 class="text-lg font-semibold tracking-tight text-stone-900">{{ __('app.profile.become_organizer') }}</h2>
                <p class="mt-1 text-sm text-stone-500">
                    @if ($organizerApplication)
                        {{ __('app.profile.application_rejected_retry') }}
                    @else
                        {{ __('app.profile.application_intro') }}
                    @endif
                </p>

                <form method="POST" action="{{ route('organizer.apply') }}" class="mt-4">
                    @csrf
                    <div class="flex flex-col gap-2">
                        <label for="message" class="text-sm font-medium text-stone-700">{{ __('app.profile.why_organize') }}</label>
                        <textarea id="message" name="message" rows="4" required
                                  minlength="{{ OrganizerApplication::MIN_MESSAGE_LENGTH }}"
                                  maxlength="{{ OrganizerApplication::MAX_MESSAGE_LENGTH }}"
                                  data-charcount-input data-charcount-min="{{ OrganizerApplication::MIN_MESSAGE_LENGTH }}"
                                  aria-describedby="message-hint"
                                  @error('message') aria-invalid="true" @enderror
                                  class="@error('message') border-bad-500 focus:border-bad-500 focus:ring-bad-500/30 @else border-stone-300 focus:border-emerald-500 focus:ring-emerald-500/30 @enderror rounded-lg border px-3 py-2 text-stone-900 outline-none transition focus:ring-2">{{ old('message') }}</textarea>

                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <span id="message-hint" class="text-xs text-stone-400">
                                {{ __('app.profile.min_chars_hint', ['min' => OrganizerApplication::MIN_MESSAGE_LENGTH]) }}
                            </span>
                            {{-- Numbers only, so it needs no translation; the hint
                                 beside it carries the words. --}}
                            <span data-charcount-output aria-hidden="true"
                                  class="text-xs tabular-nums text-stone-400"></span>
                        </div>

                        <x-field-error for="message" />
                    </div>
                    <button type="submit" data-charcount-submit
                            class="mt-4 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-stone-300 disabled:hover:bg-stone-300 disabled:active:scale-100">
                        {{ __('app.profile.send_application') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection
