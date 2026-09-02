@extends('layouts.admin')

@section('title', __('app.admin.edit_category'))

@section('content')
    <div class="reveal mx-auto max-w-lg">
        <a href="{{ route('admin.categories.index') }}" class="mb-6 inline-flex text-sm text-stone-500 transition hover:text-stone-900">&larr; {{ __('app.admin.categories') }}</a>
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.admin.edit_category') }}</h1>

        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="mt-8 space-y-5 rounded-2xl border border-stone-200 bg-white p-6">
            @csrf
            @method('PUT')
            <div class="flex flex-col gap-2">
                <label for="name" class="text-sm font-medium text-stone-700">{{ __('app.admin.name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name', $category->name) }}" required autofocus
                       class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">{{ __('app.common.save_changes') }}</button>
                <a href="{{ route('admin.categories.index') }}" class="text-sm text-stone-500 transition hover:text-stone-900">{{ __('app.common.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
