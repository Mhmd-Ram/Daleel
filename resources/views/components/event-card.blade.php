@props(['event', 'index' => 0])

<a href="{{ route('events.show', $event) }}" style="--reveal-delay: {{ ($index % 3) * 90 }}ms"
   class="reveal group block">
    <article class="event-card relative flex h-full flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white/90 shadow-sm backdrop-blur-sm">
        <div class="relative aspect-[16/10] overflow-hidden bg-stone-100">
            <img src="https://picsum.photos/seed/event-{{ $event->id }}/800/500" alt=""
                 class="h-full w-full object-cover" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/35 via-transparent to-transparent"></div>

            <div class="absolute start-3 top-3 grid place-items-center rounded-xl bg-white/95 px-3 py-1.5 text-center shadow-md shadow-stone-900/10">
                <span class="text-[10px] font-semibold uppercase tracking-wide text-emerald-600">{{ $event->start_date_time->format('M') }}</span>
                <span class="-mt-0.5 text-lg font-bold leading-none text-stone-900">{{ $event->start_date_time->format('j') }}</span>
            </div>

            <span class="absolute bottom-3 start-3 inline-flex items-center gap-1 rounded-full bg-white/90 px-2.5 py-1 text-xs font-medium text-emerald-700 shadow-sm backdrop-blur">
                <x-icon name="tag" class="h-3.5 w-3.5" /> {{ $event->category->name }}
            </span>
        </div>

        <div class="flex flex-1 flex-col p-5">
            <h3 class="text-lg font-semibold text-stone-900 transition-colors duration-300 group-hover:text-emerald-700">{{ $event->name }}</h3>

            <div class="mt-3 space-y-1.5 text-sm text-stone-500">
                <p class="flex items-center gap-2"><x-icon name="clock" class="h-4 w-4 shrink-0 text-stone-400" /> {{ $event->start_date_time->format('D, M j · g:i A') }}</p>
                <p class="flex items-center gap-2"><x-icon name="pin" class="h-4 w-4 shrink-0 text-stone-400" /> {{ $event->location }}</p>
            </div>

            <div class="mt-4 flex items-center justify-between border-t border-stone-100 pt-4">
                <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-stone-900">
                    <x-icon name="ticket" class="h-4 w-4 text-emerald-600" />
                    {{ $event->tiket_cost > 0 ? '$'.number_format($event->tiket_cost, 2) : 'Free' }}
                </span>
                <span class="inline-flex items-center gap-1 text-sm font-medium text-emerald-700">
                    {{ __('app.common.details') }}
                    <x-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1 rtl:rotate-180 rtl:group-hover:-translate-x-1" />
                </span>
            </div>
        </div>
    </article>
</a>
