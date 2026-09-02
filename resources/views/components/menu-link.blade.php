@props(['href', 'label'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'menu-link']) }}>
    <span class="menu-word">{{ $label }}<span class="menu-link__fill" aria-hidden="true">{{ $label }}</span></span>
    <x-icon name="arrow-right" class="menu-link__arrow h-7 w-7 rtl:rotate-180" />
</a>
