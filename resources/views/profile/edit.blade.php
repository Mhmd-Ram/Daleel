@extends('layouts.app')

@section('title', 'Edit profile')

@section('content')
    <div class="reveal mx-auto max-w-lg">
        <a href="{{ route('profile.show') }}" class="mb-6 inline-flex text-sm text-stone-500 transition hover:text-stone-900">&larr; Profile</a>
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Edit profile</h1>

        <form method="POST" action="{{ route('profile.update') }}" class="mt-8 space-y-5 rounded-2xl border border-stone-200 bg-white p-6">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-2">
                <label for="name" class="text-sm font-medium text-stone-700">Full name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="flex flex-col gap-2">
                <label for="email" class="text-sm font-medium text-stone-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="flex flex-col gap-2">
                <label for="phone_number" class="text-sm font-medium text-stone-700">Phone number</label>
                <input id="phone_number" name="phone_number" type="text" value="{{ old('phone_number', $user->phone_number) }}" required
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="flex flex-col gap-2">
                    <label for="dob" class="text-sm font-medium text-stone-700">Date of birth</label>
                    <input id="dob" name="dob" type="date" value="{{ old('dob', $user->dob->format('Y-m-d')) }}" required
                           class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                </div>
                <div class="flex flex-col gap-2">
                    <label for="location" class="text-sm font-medium text-stone-700">Location</label>
                    <input id="location" name="location" type="text" value="{{ old('location', $user->location) }}" required
                           class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">Save changes</button>
                <a href="{{ route('profile.show') }}" class="text-sm text-stone-500 transition hover:text-stone-900">Cancel</a>
            </div>
        </form>
    </div>
@endsection
