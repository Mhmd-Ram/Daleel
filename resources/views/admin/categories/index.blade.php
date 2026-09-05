@extends('layouts.admin')

@section('title', __('app.admin.categories'))

@section('content')
    <div class="reveal mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.admin.categories') }}</h1>
            <p class="mt-1 text-sm text-stone-500">{{ __('app.admin.categories_subtitle') }}</p>
        </div>
        <a href="{{ route('admin.categories.create') }}"
           class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
            {{ __('app.admin.new_category') }}
        </a>
    </div>

    @if ($categories->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">{{ __('app.admin.no_categories') }}</p>
            <p class="mt-1 text-stone-500">{{ __('app.admin.no_categories_body') }}</p>
        </div>
    @else
        <div class="reveal overflow-x-auto rounded-xl border border-stone-200 bg-white" style="--reveal-delay: 80ms">
            <table class="w-full text-start text-sm">
                <thead class="border-b border-stone-200 bg-stone-50 text-stone-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('app.admin.name') }}</th>
                        <th class="px-5 py-3 text-end font-medium whitespace-nowrap">{{ __('app.admin.events') }}</th>
                        <th class="px-5 py-3 text-end font-medium">{{ __('app.admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($categories as $category)
                        <tr>
                            <td class="px-5 py-3 font-medium text-stone-900">{{ $category->name }}</td>
                            <td class="px-5 py-3 text-end tabular-nums text-stone-500">{{ $category->events_count }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                       class="rounded-md border border-stone-300 px-3 py-1.5 text-stone-700 transition hover:border-stone-400">{{ __('app.common.edit') }}</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                          onsubmit="return confirm('Delete this category? Its events will be removed too.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-stone-700 transition hover:border-rose-300 hover:text-rose-700">{{ __('app.common.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $categories->links() }}
        </div>
    @endif
@endsection
