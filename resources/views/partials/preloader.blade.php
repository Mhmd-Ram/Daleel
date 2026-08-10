{{-- Intro preloader. Visibility + play-once is controlled by the inline head
     script which toggles html.eh-preloading / html.eh-loaded before paint. --}}
<div id="eh-preloader" aria-hidden="true">
    <h1 class="eh-wordmark">
        @php $letterIndex = 0; @endphp
        @foreach (str_split('EVENTS HUB') as $char)
            @if ($char === ' ')
                <span class="eh-space"></span>
            @else
                <span class="eh-letter" style="--i: {{ $letterIndex }}; --jx: {{ rand(-30, 30) / 10 }}px">{{ $char }}</span>
                @php $letterIndex++; @endphp
            @endif
        @endforeach
    </h1>
</div>
