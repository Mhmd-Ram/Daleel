@use('App\Enums\LibyanCity')

@php($e = $event ?? null)

<div class="grid gap-5 sm:grid-cols-2">
    <div class="flex flex-col gap-2 sm:col-span-2">
        <label for="name" class="text-sm font-medium text-stone-700">{{ __('app.form.event_title') }}</label>
        <input id="name" name="name" type="text" value="{{ old('name', $e?->name) }}" required
               class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
    </div>

    <div class="flex flex-col gap-2 sm:col-span-2">
        <label for="description" class="text-sm font-medium text-stone-700">{{ __('app.form.description') }}</label>
        <textarea id="description" name="description" rows="4" required
                  class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">{{ old('description', $e?->description) }}</textarea>
    </div>

    <div class="flex flex-col gap-2">
        <label for="location" class="text-sm font-medium text-stone-700">{{ __('app.form.location') }}</label>
        <input id="location" name="location" type="text" value="{{ old('location', $e?->location) }}" required
               class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
    </div>

    <div class="flex flex-col gap-2">
        <label for="city" class="text-sm font-medium text-stone-700">{{ __('app.form.city') }}</label>
        <select id="city" name="city" required
                class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            <option value="" disabled {{ old('city', $e?->city?->value) ? '' : 'selected' }}>{{ __('app.form.choose_a_city') }}</option>
            @foreach (LibyanCity::cases() as $city)
                <option value="{{ $city->value }}" @selected(old('city', $e?->city?->value) === $city->value)>{{ $city->value }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex flex-col gap-2">
        <label for="category_id" class="text-sm font-medium text-stone-700">{{ __('app.form.category') }}</label>
        <select id="category_id" name="category_id" required
                class="rounded-lg border border-stone-300 bg-white px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
            <option value="" disabled {{ old('category_id', $e?->category_id) ? '' : 'selected' }}>{{ __('app.form.choose_a_category') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('category_id', $e?->category_id) === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex flex-col gap-2">
        <label for="start_date_time" class="text-sm font-medium text-stone-700">{{ __('app.form.starts') }}</label>
        <input id="start_date_time" name="start_date_time" type="datetime-local" required
               value="{{ old('start_date_time', $e?->start_date_time?->format('Y-m-d\TH:i')) }}"
               class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
    </div>

    <div class="flex flex-col gap-2">
        <label for="end_date_time" class="text-sm font-medium text-stone-700">{{ __('app.form.ends') }}</label>
        <input id="end_date_time" name="end_date_time" type="datetime-local" required
               value="{{ old('end_date_time', $e?->end_date_time?->format('Y-m-d\TH:i')) }}"
               class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
    </div>

    <div class="flex flex-col gap-2">
        <label for="tiket_cost" class="text-sm font-medium text-stone-700">{{ __('app.form.ticket_cost') }}</label>
        <input id="tiket_cost" name="tiket_cost" type="number" step="0.01" min="0" required
               value="{{ old('tiket_cost', $e?->tiket_cost ?? '0.00') }}"
               class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
        <span class="text-xs text-stone-400">{{ __('app.form.ticket_hint') }}</span>
    </div>

    <div class="flex flex-col gap-2">
        <label for="max_capacity" class="text-sm font-medium text-stone-700">{{ __('app.form.max_capacity') }}</label>
        <input id="max_capacity" name="max_capacity" type="number" min="1"
               value="{{ old('max_capacity', $e?->max_capacity) }}"
               class="rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
        <span class="text-xs text-stone-400">{{ __('app.form.capacity_hint') }}</span>
    </div>

    <label class="flex items-center gap-3 sm:col-span-2">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $e?->is_active))
               class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500/30">
        <span class="text-sm text-stone-700">{{ __('app.form.published_label') }}</span>
    </label>
</div>

{{-- Map pin. Optional: an event without one simply shows no map. --}}
<div class="mt-5 flex flex-col gap-2">
    <div class="flex flex-wrap items-baseline justify-between gap-2">
        <span class="text-sm font-medium text-stone-700">{{ __('app.form.pin_on_map') }}</span>
        <span class="text-xs text-stone-400">{{ __('app.form.pin_hint') }}</span>
    </div>

    <div class="flex flex-col gap-2 sm:flex-row">
        <input type="text" data-map-search="event-map" placeholder="{{ __('app.form.address_search_placeholder') }}"
               aria-label="{{ __('app.form.address_search_label') }}"
               class="flex-1 rounded-lg border border-stone-300 px-3 py-2 text-stone-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">
        {{-- type="button" matters: this must not submit the event form. --}}
        <button type="button" data-map-search-go="event-map"
                class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-stone-400 hover:text-stone-900">
            {{ __('app.form.search') }}
        </button>
        <button type="button" data-map-clear="event-map"
                class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-rose-300 hover:text-rose-700">
            {{ __('app.form.clear_pin') }}
        </button>
    </div>

    <p data-map-search-status="event-map" role="status" aria-live="polite"
       class="min-h-[1.25rem] text-xs text-stone-500"></p>

    <div id="event-map" data-map-picker
         data-map-lat-input="latitude" data-map-lng-input="longitude"
         class="h-72 w-full overflow-hidden rounded-xl border border-stone-200 bg-stone-100"></div>

    <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $e?->latitude) }}">
    <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $e?->longitude) }}">

    <noscript>
        <p class="text-xs text-stone-500">
            {{ __('app.form.noscript') }}
        </p>
    </noscript>
</div>

@push('scripts')
    @vite(['resources/js/map.js'])
@endpush
