@extends('layouts.app')

@section('title', __('app.events.unavailable_title'))

@section('content')
    <div class="reveal mx-auto max-w-lg">
        <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">{{ __('app.events.unavailable_heading') }}</p>
            <p class="mt-1 text-stone-500">
                {{ __('app.events.unavailable_body') }}
            </p>
            <a href="{{ route('events.index') }}"
               class="mt-5 inline-block rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
                {{ __('app.events.browse_events') }}
            </a>
        </div>
    </div>
@endsection
