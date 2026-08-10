@extends('layouts.admin')

@section('title', 'Edit event')

@section('content')
    <div class="reveal mx-auto max-w-2xl">
        <a href="{{ route('admin.events.index') }}" class="mb-6 inline-flex text-sm text-stone-500 transition hover:text-stone-900">&larr; Events</a>
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Edit event</h1>

        <form method="POST" action="{{ route('admin.events.update', $event) }}" class="mt-8 rounded-2xl border border-stone-200 bg-white p-6">
            @csrf
            @method('PUT')
            @include('admin.events._form')
            <div class="mt-6 flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">Save changes</button>
                <a href="{{ route('admin.events.index') }}" class="text-sm text-stone-500 transition hover:text-stone-900">Cancel</a>
            </div>
        </form>
    </div>
@endsection
