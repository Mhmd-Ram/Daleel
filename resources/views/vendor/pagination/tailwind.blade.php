{{--
    Replaces Laravel's stock Tailwind paginator, which did not survive contact
    with this app: it is styled in `gray-*`/`blue-*` (neither exists in the
    cream-and-claret palette), carries `dark:` variants the app has no use for,
    hard-codes `rounded-l-md` / `-ml-px` / left-and-right chevrons so the button
    group breaks apart under `dir="rtl"`, and leaves five English strings
    untranslated in a bilingual app.

    Separate rounded buttons rather than one joined pill: the join relied on a
    -1px margin pulling each button onto its neighbour, which is the part that
    inverts badly in Arabic.
--}}
@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('pagination.navigation') }}"
         class="flex flex-wrap items-center justify-between gap-4">

        <p class="text-sm text-stone-500">
            @if ($paginator->firstItem())
                {{ __('pagination.showing_range', [
                    'first' => $paginator->firstItem(),
                    'last' => $paginator->lastItem(),
                    'total' => $paginator->total(),
                ]) }}
            @else
                {{ __('pagination.showing_total', ['total' => $paginator->total()]) }}
            @endif
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}"
                      class="inline-flex cursor-not-allowed items-center rounded-lg border border-stone-200 px-2.5 py-2 text-stone-300">
                    <x-icon name="chevron-left" class="h-4 w-4 rtl:rotate-180" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}"
                   class="inline-flex items-center rounded-lg border border-stone-300 px-2.5 py-2 text-stone-600 transition hover:border-stone-400 hover:text-stone-900">
                    <x-icon name="chevron-left" class="h-4 w-4 rtl:rotate-180" />
                </a>
            @endif

            {{-- Page numbers are hidden on narrow screens; the arrows and the
                 summary line above are enough to navigate there. --}}
            <div class="hidden items-center gap-1 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-2 text-sm text-stone-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="inline-flex min-w-9 justify-center rounded-lg border border-emerald-600 bg-emerald-600 px-3 py-1.5 text-sm font-medium text-white">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" aria-label="{{ __('pagination.go_to_page', ['page' => $page]) }}"
                                   class="inline-flex min-w-9 justify-center rounded-lg border border-stone-300 px-3 py-1.5 text-sm text-stone-600 transition hover:border-stone-400 hover:text-stone-900">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}"
                   class="inline-flex items-center rounded-lg border border-stone-300 px-2.5 py-2 text-stone-600 transition hover:border-stone-400 hover:text-stone-900">
                    <x-icon name="chevron-right" class="h-4 w-4 rtl:rotate-180" />
                </a>
            @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}"
                      class="inline-flex cursor-not-allowed items-center rounded-lg border border-stone-200 px-2.5 py-2 text-stone-300">
                    <x-icon name="chevron-right" class="h-4 w-4 rtl:rotate-180" />
                </span>
            @endif
        </div>
    </nav>
@endif
