@extends('layouts.admin')

@section('title', __('app.common.edit_event'))

@section('content')
    <div class="reveal mx-auto max-w-2xl">
        <a href="{{ route('admin.events.index') }}" class="mb-6 inline-flex text-sm text-stone-500 transition hover:text-stone-900">&larr; {{ __('app.admin.events') }}</a>
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.common.edit_event') }}</h1>

        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.events.update', $event) }}" class="mt-8 rounded-2xl border border-stone-200 bg-white p-6">
            @csrf
            @method('PUT')
            @include('partials.event-form')
            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">{{ __('app.common.save_changes') }}</button>
                <a href="{{ route('admin.events.index') }}" class="text-sm text-stone-500 transition hover:text-stone-900">{{ __('app.common.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
