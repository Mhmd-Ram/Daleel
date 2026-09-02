@extends('layouts.admin')

@section('title', __('app.admin.login_title'))

@section('content')
    <div class="reveal mx-auto max-w-md">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.admin.sign_in_heading') }}</h1>
        <p class="mt-1 text-sm text-stone-500">{{ __('app.admin.sign_in_subtitle') }}</p>

        <form method="POST" action="{{ route('admin.login') }}" autocomplete="off" class="mt-8 space-y-5 rounded-2xl border border-stone-200 bg-white p-6">
            @csrf

            <div class="flex flex-col gap-2">
                <label for="email" class="text-sm font-medium text-stone-700">{{ __('app.common.email') }}</label>
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
                    class="w-full rounded-lg bg-stone-900 px-4 py-2.5 font-medium text-white transition hover:bg-stone-800 active:scale-[0.98]">
                {{ __('app.admin.sign_in') }}
            </button>
        </form>
    </div>
@endsection
