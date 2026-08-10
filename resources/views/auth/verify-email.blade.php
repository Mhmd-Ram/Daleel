@extends('layouts.app')

@section('title', 'Verify your email')

@section('content')
    <div class="reveal mx-auto max-w-md">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Verify your email</h1>
        <p class="mt-1 text-sm text-stone-500">One more step before you can register for events.</p>

        <div class="mt-8 rounded-2xl border border-stone-200 bg-white p-6">
            <p class="text-sm leading-relaxed text-stone-600">
                We sent a verification link to
                <span class="font-medium text-stone-900">{{ auth()->user()->email }}</span>.
                Open it to activate your account. If it has not arrived, check your spam folder
                or send yourself a new one.
            </p>

            <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
                @csrf
                <button type="submit"
                        class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                    Resend the link
                </button>
            </form>

            <div class="mt-4 flex items-center justify-between text-sm">
                <a href="{{ route('profile.edit') }}" class="font-medium text-emerald-700 hover:underline">
                    Wrong address? Update it
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-stone-500 transition hover:text-stone-900">Log out</button>
                </form>
            </div>
        </div>
    </div>
@endsection
