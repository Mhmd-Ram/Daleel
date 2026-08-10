@extends('layouts.app')

@section('title', 'My organized events')

@section('content')
    <div class="reveal mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-stone-900">Events you organize</h1>
            <p class="mt-1 text-sm text-stone-500">Create, publish and manage the events you run.</p>
        </div>
        <a href="{{ route('organizer.events.create') }}"
           class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-700 active:scale-[0.98]">
            New event
        </a>
    </div>

    @if ($events->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">No events yet</p>
            <p class="mt-1 text-stone-500">Create your first event and publish it when you are ready.</p>
        </div>
    @else
        <div class="reveal overflow-hidden rounded-xl border border-stone-200 bg-white" style="--reveal-delay: 80ms">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-stone-200 bg-stone-50 text-stone-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Event</th>
                        <th class="px-5 py-3 font-medium">Category</th>
                        <th class="px-5 py-3 font-medium">Starts</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Registered</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($events as $event)
                        <tr>
                            <td class="px-5 py-3 font-medium text-stone-900">{{ $event->name }}</td>
                            <td class="px-5 py-3 text-stone-500">{{ $event->category->name }}</td>
                            <td class="px-5 py-3 text-stone-500">{{ $event->start_date_time->format('M j, Y g:i A') }}</td>
                            <td class="px-5 py-3">
                                @if ($event->is_active)
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Published</span>
                                @else
                                    <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-500">Draft</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-stone-500">{{ $event->registered_users_count }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('events.show', $event) }}"
                                       class="rounded-md border border-stone-300 px-3 py-1.5 text-stone-700 transition hover:border-stone-400">View</a>
                                    <a href="{{ route('organizer.events.edit', $event) }}"
                                       class="rounded-md border border-stone-300 px-3 py-1.5 text-stone-700 transition hover:border-stone-400">Edit</a>
                                    <form method="POST" action="{{ route('organizer.events.destroy', $event) }}"
                                          onsubmit="return confirm('Delete this event? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-stone-300 px-3 py-1.5 text-stone-700 transition hover:border-rose-300 hover:text-rose-700">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
