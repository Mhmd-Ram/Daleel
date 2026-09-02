@extends('layouts.admin')

@section('title', __('app.admin.users'))

@section('content')
    <div class="reveal mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-stone-900">{{ __('app.admin.user_management') }}</h1>
        <p class="mt-1 text-sm text-stone-500">{{ __('app.admin.users_subtitle') }}</p>
    </div>

    {{-- Filters (FR-10.1, FR-10.2). GET, so a filtered view stays linkable. --}}
    <form method="GET" action="{{ route('admin.users.index') }}"
          class="reveal mb-6 rounded-xl border border-stone-200 bg-white p-4" style="--reveal-delay: 40ms">
        <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_auto_auto_auto]">
            <div>
                <label for="q" class="sr-only">{{ __('app.admin.search_users_label') }}</label>
                <input id="q" name="q" type="search" value="{{ $keyword }}"
                       placeholder="{{ __('app.admin.search_users_placeholder') }}"
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-900 placeholder:text-stone-500 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            </div>

            <div>
                <label for="role" class="sr-only">{{ __('app.admin.filter_by_role') }}</label>
                <select id="role" name="role"
                        class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                    <option value="">{{ __('app.admin.all_roles') }}</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected($selectedRole === $role)>{{ ucfirst($role->value) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="sr-only">{{ __('app.admin.filter_by_status') }}</label>
                <select id="status" name="status"
                        class="w-full rounded-lg border border-stone-300 bg-white px-3 py-2 text-sm text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
                    <option value="" @selected($selectedStatus === null)>{{ __('app.admin.all_status') }}</option>
                    <option value="active" @selected($selectedStatus === 'active')>{{ __('app.admin.active') }}</option>
                    <option value="banned" @selected($selectedStatus === 'banned')>{{ __('app.admin.banned') }}</option>
                </select>
            </div>

            <button type="submit"
                    class="rounded-lg border border-emerald-600 bg-emerald-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 active:translate-y-px">
                {{ __('app.admin.filter') }}
            </button>
        </div>
    </form>

    @if ($users->isEmpty())
        <div class="rounded-xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
            <p class="text-lg font-medium text-stone-900">{{ __('app.admin.no_users_match') }}</p>
            <p class="mt-1 text-stone-500">{{ __('app.admin.no_users_match_body') }}</p>
        </div>
    @else
        <div class="reveal overflow-hidden rounded-xl border border-stone-200 bg-white" style="--reveal-delay: 80ms">
            <table class="w-full text-start text-sm">
                <thead class="border-b border-stone-200 bg-stone-50 text-stone-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">{{ __('app.admin.user') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.common.email') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.admin.role') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.common.status') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.admin.joined') }}</th>
                        <th class="px-5 py-3 font-medium">{{ __('app.admin.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($users as $listedUser)
                        <tr>
                            <td class="px-5 py-3">
                                <span class="font-medium text-stone-900">{{ $listedUser->name }}</span>
                                <span class="block text-xs text-stone-400">{{ __('app.admin.saved_count', ['count' => $listedUser->registrations_count]) }}</span>
                            </td>
                            <td class="px-5 py-3 text-stone-500">{{ $listedUser->email }}</td>
                            <td class="px-5 py-3">
                                @if ($listedUser->isOrganizer())
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">{{ __('app.admin.organizer') }}</span>
                                @else
                                    <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-0.5 text-xs font-medium text-stone-500">{{ __('app.admin.attendee') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($listedUser->isBanned())
                                    <span class="inline-flex rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-medium text-rose-700">{{ __('app.admin.banned') }}</span>
                                @else
                                    <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">{{ __('app.admin.active') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-stone-500">{{ $listedUser->created_at->format('M j, Y') }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    @if ($listedUser->isOrganizer())
                                        <form method="POST" action="{{ route('admin.users.revert-role', $listedUser) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-medium text-stone-700 transition hover:border-stone-400 hover:text-stone-900">
                                                {{ __('app.admin.revert_role') }}
                                            </button>
                                        </form>
                                    @endif

                                    @if ($listedUser->isBanned())
                                        <form method="POST" action="{{ route('admin.users.unban', $listedUser) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-lg border border-stone-300 px-3 py-1.5 text-xs font-medium text-stone-700 transition hover:border-stone-400 hover:text-stone-900">
                                                {{ __('app.admin.restore') }}
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.ban', $listedUser) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700 transition hover:border-rose-300 hover:bg-rose-100">
                                                {{ __('app.admin.suspend') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $users->links() }}
        </div>
    @endif
@endsection
