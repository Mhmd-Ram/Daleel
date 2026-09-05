@use('App\Enums\LibyanCity')

@php($e = $event ?? null)

<div class="grid gap-5 sm:grid-cols-2">
    <div class="flex flex-col gap-2 sm:col-span-2">
        <label for="name" class="text-sm font-medium text-stone-700">{{ __('app.form.event_title') }}</label>
        <input id="name" name="name" type="text" value="{{ old('name', $e?->name) }}" required
               class="@error('name') border-bad-500 focus:border-bad-500 focus:ring-bad-500/30 @else border-stone-300 focus:border-emerald-500 focus:ring-emerald-500/30 @enderror rounded-lg border px-3 py-2 text-stone-900 outline-none transition focus:ring-2">
        <x-field-error for="name" />
    </div>

    <div class="flex flex-col gap-2 sm:col-span-2">
        <label for="description" class="text-sm font-medium text-stone-700">{{ __('app.form.description') }}</label>
        <textarea id="description" name="description" rows="4" required
                  class="@error('description') border-bad-500 focus:border-bad-500 focus:ring-bad-500/30 @else border-stone-300 focus:border-emerald-500 focus:ring-emerald-500/30 @enderror rounded-lg border px-3 py-2 text-stone-900 outline-none transition focus:ring-2">{{ old('description', $e?->description) }}</textarea>
        <x-field-error for="description" />
    </div>

    <div class="flex flex-col gap-2">
        <label for="location" class="text-sm font-medium text-stone-700">{{ __('app.form.location') }}</label>
        <input id="location" name="location" type="text" value="{{ old('location', $e?->location) }}" required
               class="@error('location') border-bad-500 focus:border-bad-500 focus:ring-bad-500/30 @else border-stone-300 focus:border-emerald-500 focus:ring-emerald-500/30 @enderror rounded-lg border px-3 py-2 text-stone-900 outline-none transition focus:ring-2">
        <x-field-error for="location" />
    </div>

    <div class="flex flex-col gap-2">
        <label for="city" class="text-sm font-medium text-stone-700">{{ __('app.form.city') }}</label>
        <select id="city" name="city" required
                class="@error('city') border-bad-500 focus:border-bad-500 focus:ring-bad-500/30 @else border-stone-300 focus:border-emerald-500 focus:ring-emerald-500/30 @enderror rounded-lg border bg-white px-3 py-2 text-stone-900 outline-none transition focus:ring-2">
            <option value="" disabled {{ old('city', $e?->city?->value) ? '' : 'selected' }}>{{ __('app.form.choose_a_city') }}</option>
            @foreach (LibyanCity::cases() as $city)
                <option value="{{ $city->value }}" @selected(old('city', $e?->city?->value) === $city->value)>{{ $city->value }}</option>
            @endforeach
        </select>
        <x-field-error for="city" />
    </div>

    <div class="flex flex-col gap-2">
        <label for="category_id" class="text-sm font-medium text-stone-700">{{ __('app.form.category') }}</label>
        <select id="category_id" name="category_id" required
                class="@error('category_id') border-bad-500 focus:border-bad-500 focus:ring-bad-500/30 @else border-stone-300 focus:border-emerald-500 focus:ring-emerald-500/30 @enderror rounded-lg border bg-white px-3 py-2 text-stone-900 outline-none transition focus:ring-2">
            <option value="" disabled {{ old('category_id', $e?->category_id) ? '' : 'selected' }}>{{ __('app.form.choose_a_category') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('category_id', $e?->category_id) === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <x-field-error for="category_id" />
    </div>

    <div class="flex flex-col gap-2">
        <label for="start_date_time" class="text-sm font-medium text-stone-700">{{ __('app.form.starts') }}</label>
        <input id="start_date_time" name="start_date_time" type="datetime-local" required
               value="{{ old('start_date_time', $e?->start_date_time?->format('Y-m-d\TH:i')) }}"
               class="@error('start_date_time') border-bad-500 focus:border-bad-500 focus:ring-bad-500/30 @else border-stone-300 focus:border-emerald-500 focus:ring-emerald-500/30 @enderror rounded-lg border px-3 py-2 text-stone-900 outline-none transition focus:ring-2">
        <x-field-error for="start_date_time" />
    </div>

    <div class="flex flex-col gap-2">
        <label for="end_date_time" class="text-sm font-medium text-stone-700">{{ __('app.form.ends') }}</label>
        <input id="end_date_time" name="end_date_time" type="datetime-local" required
               value="{{ old('end_date_time', $e?->end_date_time?->format('Y-m-d\TH:i')) }}"
               class="@error('end_date_time') border-bad-500 focus:border-bad-500 focus:ring-bad-500/30 @else border-stone-300 focus:border-emerald-500 focus:ring-emerald-500/30 @enderror rounded-lg border px-3 py-2 text-stone-900 outline-none transition focus:ring-2">
        <x-field-error for="end_date_time" />
    </div>

    <div class="flex flex-col gap-2">
        <label for="tiket_cost" class="text-sm font-medium text-stone-700">{{ __('app.form.ticket_cost') }}</label>
        <input id="tiket_cost" name="tiket_cost" type="number" step="0.01" min="0" required
               value="{{ old('tiket_cost', $e?->tiket_cost ?? '0.00') }}"
               class="@error('tiket_cost') border-bad-500 focus:border-bad-500 focus:ring-bad-500/30 @else border-stone-300 focus:border-emerald-500 focus:ring-emerald-500/30 @enderror rounded-lg border px-3 py-2 text-stone-900 outline-none transition focus:ring-2">
        <span class="text-xs text-stone-400">{{ __('app.form.ticket_hint') }}</span>
        <x-field-error for="tiket_cost" />
    </div>

    <div class="flex flex-col gap-2">
        <label for="max_capacity" class="text-sm font-medium text-stone-700">{{ __('app.form.max_capacity') }}</label>
        <input id="max_capacity" name="max_capacity" type="number" min="1"
               value="{{ old('max_capacity', $e?->max_capacity) }}"
               class="@error('max_capacity') border-bad-500 focus:border-bad-500 focus:ring-bad-500/30 @else border-stone-300 focus:border-emerald-500 focus:ring-emerald-500/30 @enderror rounded-lg border px-3 py-2 text-stone-900 outline-none transition focus:ring-2">
        <span class="text-xs text-stone-400">{{ __('app.form.capacity_hint') }}</span>
        <x-field-error for="max_capacity" />
    </div>

    {{-- Cover image. Optional: an event without one keeps showing the
         placeholder, so nothing breaks for events created before uploads. --}}
    <div data-image-field class="flex flex-col gap-2 sm:col-span-2">
        <label for="image" class="text-sm font-medium text-stone-700">{{ __('app.form.cover_image') }}</label>

        <div class="flex flex-wrap items-start gap-4">
            {{-- Two layers: the empty state shows until there is something to
                 show, and the preview hides itself until it has a source. An
                 empty `src` would otherwise render as a broken image. --}}
            <div class="relative grid h-24 w-40 shrink-0 place-items-center overflow-hidden rounded-lg border border-stone-200 bg-stone-100">
                <span data-image-empty @if ($e?->image_path) hidden @endif
                      class="flex flex-col items-center gap-1 text-stone-400">
                    <x-icon name="photo" class="h-6 w-6" />
                    <span class="text-[10px] uppercase tracking-wide">{{ __('app.form.no_image') }}</span>
                </span>

                <img data-image-preview alt=""
                     @if ($e?->image_path) src="{{ $e->imageUrl(400, 250) }}" @else hidden @endif
                     class="absolute inset-0 h-full w-full object-cover">
            </div>

            <div class="flex min-w-56 flex-1 flex-col gap-2">
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
                       data-image-input
                       @error('image') aria-invalid="true" @enderror
                       class="rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-700 outline-none transition file:me-3 file:rounded-md file:border-0 file:bg-stone-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-stone-700 hover:file:bg-stone-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/30">

                <span class="text-xs text-stone-400">{{ __('app.form.cover_image_hint') }}</span>

                @if ($e?->image_path)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))
                               class="rounded border-stone-300 text-emerald-600 focus:ring-emerald-500/30">
                        <span class="text-sm text-stone-700">{{ __('app.form.remove_image') }}</span>
                    </label>
                @endif

                <x-field-error for="image" />
            </div>
        </div>
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
