@extends('layouts.app')

@section('title', __('app.auth.login_title'))

@section('content')
    <div class="reveal mx-auto max-w-md">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.auth.welcome_back') }}</h1>
        <p class="mt-1 text-sm text-stone-500">{{ __('app.auth.login_subtitle') }}</p>

        <form method="POST" action="{{ route('login') }}" autocomplete="off" class="mt-8 space-y-5 rounded-2xl border border-stone-200 bg-white p-6">
            @csrf

            <div class="flex flex-col gap-2">
                <label for="email" class="text-sm font-medium text-stone-700">{{ __('app.auth.email') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="off"
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="flex flex-col gap-2">
                <label for="password" class="text-sm font-medium text-stone-700">{{ __('app.auth.password') }}</label>
                <input id="password" name="password" type="password" required autocomplete="off" data-1p-ignore data-lpignore="true"
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <label class="flex items-center gap-2 text-sm text-stone-600">
                <input type="checkbox" name="remember" class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500/30">
                {{ __('app.auth.remember_me') }}
            </label>

            <button type="submit"
                    class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                {{ __('app.auth.login_title') }}
            </button>

            <p class="text-center text-sm text-stone-500">
                {{ __('app.auth.new_here') }}
                <a href="{{ route('register') }}" class="font-medium text-emerald-700 hover:underline">{{ __('app.auth.create_an_account') }}</a>
            </p>
        </form>
    </div>
@endsection
