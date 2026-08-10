@extends('layouts.app')

@section('title', 'Sign up')

@section('content')
    <div class="reveal mx-auto max-w-md">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Create your account</h1>
        <p class="mt-1 text-sm text-stone-500">Join to register for events and keep track of what you're attending.</p>

        <form method="POST" action="{{ route('register') }}" autocomplete="off" class="mt-8 space-y-5 rounded-2xl border border-stone-200 bg-white p-6">
            @csrf

            <div class="flex flex-col gap-2">
                <label for="name" class="text-sm font-medium text-stone-700">Full name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="off"
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="flex flex-col gap-2">
                <label for="email" class="text-sm font-medium text-stone-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="off"
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="flex flex-col gap-2">
                <label for="phone_number" class="text-sm font-medium text-stone-700">Phone number</label>
                <input id="phone_number" name="phone_number" type="text" value="{{ old('phone_number') }}" required
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label for="dob" class="text-sm font-medium text-stone-700">Date of birth</label>
                    <input id="dob" name="dob" type="date" value="{{ old('dob') }}" required
                           class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                </div>
                <div class="flex flex-col gap-2">
                    <label for="location" class="text-sm font-medium text-stone-700">Location</label>
                    <input id="location" name="location" type="text" value="{{ old('location') }}" required
                           class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <label for="password" class="text-sm font-medium text-stone-700">Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" data-1p-ignore data-lpignore="true"
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="flex flex-col gap-2">
                <label for="password_confirmation" class="text-sm font-medium text-stone-700">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" data-1p-ignore data-lpignore="true"
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                Create account
            </button>

            <p class="text-center text-sm text-stone-500">
                Already have an account?
                <a href="{{ route('login') }}" class="font-medium text-emerald-700 hover:underline">Log in</a>
            </p>
        </form>
    </div>
@endsection
