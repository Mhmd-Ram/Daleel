{{--
    Status banners, shared by the public and admin layouts.

    Colours come from the `ok-*` / `bad-*` scales, never `emerald-*` / `rose-*`:
    the theme remaps `emerald-*` to claret red, so the conventional "emerald
    means success" would paint a successful save the same colour as a failure.
    The icon carries the meaning too, for anyone who cannot rely on hue.
--}}
@if (session('success'))
    <div role="status" class="mb-6 flex items-start gap-3 rounded-lg border border-ok-200 bg-ok-50 px-4 py-3 text-sm text-ok-700">
        <x-icon name="check-circle" class="mt-px h-5 w-5 shrink-0" />
        <p>{{ session('success') }}</p>
    </div>
@endif

@if (session('error'))
    <div role="alert" class="mb-6 flex items-start gap-3 rounded-lg border border-bad-200 bg-bad-50 px-4 py-3 text-sm text-bad-700">
        <x-icon name="alert" class="mt-px h-5 w-5 shrink-0" />
        <p>{{ session('error') }}</p>
    </div>
@endif

{{-- The catch-all summary. Fields that render their own <x-field-error> still
     appear here, which is deliberate: the summary is what a screen-reader user
     hears first, and it covers errors with no field on screen. --}}
@if ($errors->any())
    <div role="alert" class="mb-6 flex items-start gap-3 rounded-lg border border-bad-200 bg-bad-50 px-4 py-3 text-sm text-bad-700">
        <x-icon name="alert" class="mt-px h-5 w-5 shrink-0" />
        <div>
            <p class="font-medium">{{ __('app.flash.fix_the_following') }}</p>
            <ul class="mt-2 list-disc space-y-1 ps-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
