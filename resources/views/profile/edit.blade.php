@extends('layouts.app')

@use('App\Enums\LibyanCity')
@use('Illuminate\Support\Str')

@section('title', __('app.profile.edit_title'))

@section('content')
    <div class="reveal mx-auto max-w-lg">
        <a href="{{ route('profile.show') }}" class="mb-6 inline-flex text-sm text-stone-500 transition hover:text-stone-900">&larr; {{ __('app.nav.profile') }}</a>
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.profile.edit_title') }}</h1>

        <form method="POST" action="{{ route('profile.update') }}" class="mt-8 space-y-5 rounded-2xl border border-stone-200 bg-white p-6">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-2">
                <label for="name" class="text-sm font-medium text-stone-700">{{ __('app.auth.full_name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="flex flex-col gap-2">
                <label for="email" class="text-sm font-medium text-stone-700">{{ __('app.auth.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="flex flex-col gap-2">
                <label for="phone_number" class="text-sm font-medium text-stone-700">{{ __('app.auth.phone_number') }}</label>
                <input id="phone_number" name="phone_number" type="text" value="{{ old('phone_number', $user->phone_number) }}" required
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label for="dob" class="text-sm font-medium text-stone-700">{{ __('app.auth.date_of_birth') }}</label>
                    <input id="dob" name="dob" type="date" value="{{ old('dob', $user->dob->format('Y-m-d')) }}" required
                           class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                </div>
                <div class="flex flex-col gap-2">
                    <label for="location" class="text-sm font-medium text-stone-700">{{ __('app.auth.location') }}</label>
                    @php($selectedCity = old('location', $user->location?->value))
                    <select id="location" name="location" required
                            class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                        <option value="" disabled @selected(! $selectedCity)>{{ __('app.auth.choose_a_city') }}</option>
                        @foreach (LibyanCity::cases() as $city)
                            <option value="{{ $city->value }}" @selected($selectedCity === $city->value)>{{ $city->value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">{{ __('app.common.save_changes') }}</button>
                <a href="{{ route('profile.show') }}" class="text-sm text-stone-500 transition hover:text-stone-900">{{ __('app.common.cancel') }}</a>
            </div>
        </form>

        {{-- Change password (FR-3.2, FR-3.3). --}}
        <form method="POST" action="{{ route('profile.password') }}" class="mt-6 space-y-5 rounded-2xl border border-stone-200 bg-white p-6">
            @csrf
            @method('PUT')

            <div>
                <h2 class="text-lg font-medium text-stone-900">{{ __('app.profile.change_password') }}</h2>
                <p class="mt-1 text-sm text-stone-500">{{ __('app.profile.stay_signed_in') }}</p>
            </div>

            <div class="flex flex-col gap-2">
                <label for="current_password" class="text-sm font-medium text-stone-700">{{ __('app.profile.current_password') }}</label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="flex flex-col gap-2">
                <label for="password" class="text-sm font-medium text-stone-700">{{ __('app.profile.new_password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="flex flex-col gap-2">
                <label for="password_confirmation" class="text-sm font-medium text-stone-700">{{ __('app.profile.confirm_new_password') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                {{ __('app.profile.update_password') }}
            </button>
        </form>

        {{-- Danger zone (UC4, FR-3.4 - FR-3.6). A details disclosure rather than a
             JS modal: this codebase ships almost no JavaScript. --}}
        <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50/50 p-6">
            <h2 class="text-lg font-medium text-rose-900">{{ __('app.profile.delete_account') }}</h2>
            <p class="mt-1 text-sm text-stone-700">
                {{ __('app.profile.delete_body') }}
            </p>

            @if ($user->isOrganizer())
                {{-- Inline assignment form, matching the location field above. A
                     block form here would pair with that earlier inline directive
                     and swallow every line between the two, because raw blocks are
                     extracted before anything else is compiled. --}}
                @php($organizedCount = $user->organized_events_count)
                @php($organizedPronoun = $organizedCount === 1 ? 'it' : 'them')
                {{-- Named explicitly: deleting the events cascades to every saved
                     entry other people hold on them, which the user cannot see.
                     Kept on one line so the sentence renders without stray breaks. --}}
                <p class="mt-3 text-sm font-medium text-rose-900">
                    {{ trans_choice('app.profile.organized_warning', $organizedCount, ['count' => $organizedCount]) }}
                </p>
            @endif

            <details class="group mt-4">
                <summary class="cursor-pointer text-sm font-medium text-rose-800 transition hover:text-rose-900">
                    {{ __('app.profile.delete_my_account') }}
                </summary>

                <form method="POST" action="{{ route('profile.destroy') }}" class="mt-4 space-y-4">
                    @csrf
                    @method('DELETE')

                    <div class="flex flex-col gap-2">
                        <label for="delete_password" class="text-sm font-medium text-stone-700">{{ __('app.profile.confirm_your_password') }}</label>
                        <input id="delete_password" name="password" type="password" required autocomplete="current-password"
                               class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-stone-900 outline-none transition focus:border-rose-500 focus:ring-2 focus:ring-rose-500/30">
                    </div>

                    <button type="submit" class="rounded-lg bg-rose-700 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-rose-800 active:scale-[0.98]">
                        {{ __('app.profile.permanently_delete') }}
                    </button>
                </form>
            </details>
        </div>
    </div>
@endsection
