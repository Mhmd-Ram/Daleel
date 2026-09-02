@extends('layouts.app')

@use('App\Enums\LibyanCity')

@section('title', __('app.auth.register_title'))

@section('content')
    <div class="reveal mx-auto max-w-md">
        <img src="{{ asset('wordmark.png') }}" alt="{{ config('app.name') }}"
             width="144" height="49" class="mb-6 h-11 w-auto">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.auth.create_your_account') }}</h1>
        <p class="mt-1 text-sm text-stone-500">{{ __('app.auth.register_subtitle') }}</p>

        <form method="POST" action="{{ route('register') }}" autocomplete="off" class="mt-8 space-y-5 rounded-2xl border border-stone-200 bg-white p-6">
            @csrf

            <div class="flex flex-col gap-2">
                <label for="name" class="text-sm font-medium text-stone-700">{{ __('app.auth.full_name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="off"
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="flex flex-col gap-2">
                <label for="email" class="text-sm font-medium text-stone-700">{{ __('app.auth.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="off"
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="flex flex-col gap-2">
                <label for="phone_number" class="text-sm font-medium text-stone-700">{{ __('app.auth.phone_number') }}</label>
                <input id="phone_number" name="phone_number" type="text" value="{{ old('phone_number') }}" required
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label for="dob" class="text-sm font-medium text-stone-700">{{ __('app.auth.date_of_birth') }}</label>
                    <input id="dob" name="dob" type="date" value="{{ old('dob') }}" required
                           class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                </div>
                <div class="flex flex-col gap-2">
                    <label for="location" class="text-sm font-medium text-stone-700">{{ __('app.auth.location') }}</label>
                    <select id="location" name="location" required
                            class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                        <option value="" disabled @selected(! old('location'))>{{ __('app.auth.choose_a_city') }}</option>
                        @foreach (LibyanCity::cases() as $city)
                            <option value="{{ $city->value }}" @selected(old('location') === $city->value)>{{ $city->value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <label for="password" class="text-sm font-medium text-stone-700">{{ __('app.auth.password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" data-1p-ignore data-lpignore="true"
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="flex flex-col gap-2">
                <label for="password_confirmation" class="text-sm font-medium text-stone-700">{{ __('app.auth.confirm_password') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" data-1p-ignore data-lpignore="true"
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                {{ __('app.auth.create_account') }}
            </button>

            <p class="text-center text-sm text-stone-500">
                {{ __('app.auth.already_have_an_account') }}
                <a href="{{ route('login') }}" class="font-medium text-emerald-700 hover:underline">{{ __('app.auth.login_title') }}</a>
            </p>
        </form>
    </div>
@endsection
