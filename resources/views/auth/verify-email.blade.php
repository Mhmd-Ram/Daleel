@extends('layouts.app')

@section('title', __('app.auth.verify_title'))

@section('content')
    <div class="reveal mx-auto max-w-md">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.auth.verify_title') }}</h1>
        <p class="mt-1 text-sm text-stone-500">{{ __('app.auth.verify_subtitle') }}</p>

        <div class="mt-8 rounded-2xl border border-stone-200 bg-white p-6">
            <p class="text-sm leading-relaxed text-stone-600">
                {{ __('app.auth.verify_sent_to') }}
                <span class="font-medium text-stone-900">{{ auth()->user()->email }}</span>.
                {{ __('app.auth.verify_body') }}
            </p>

            <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
                @csrf
                <button type="submit"
                        class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                    {{ __('app.auth.resend_the_link') }}
                </button>
            </form>

            <div class="mt-4 flex items-center justify-between text-sm">
                <a href="{{ route('profile.edit') }}" class="font-medium text-emerald-700 hover:underline">
                    {{ __('app.auth.wrong_address') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-stone-500 transition hover:text-stone-900">{{ __('app.nav.log_out') }}</button>
                </form>
            </div>
        </div>
    </div>
@endsection
