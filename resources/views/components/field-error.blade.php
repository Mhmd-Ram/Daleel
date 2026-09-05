{{--
    The validation message for one field, rendered next to the field itself.

    Pair it with `@error('name') border-bad-500 @enderror` on the input and
    `aria-invalid` / `aria-describedby`, so the failure is visible, announced,
    and attached to the control that caused it.
--}}
@props(['for'])

@error($for)
    <p id="{{ $for }}-error" role="alert" class="flex items-center gap-1.5 text-xs font-medium text-bad-700">
        <x-icon name="alert" class="h-3.5 w-3.5 shrink-0" />
        {{ $message }}
    </p>
@enderror
