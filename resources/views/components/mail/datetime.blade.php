{{--
    A date and time in the reader's language.

    `format()` is locale-blind and always renders English day and month names,
    which is how "Thursday, Sep 17" ended up inside otherwise Arabic emails.
    `translatedFormat()` with an explicit locale fixes that; the locale is passed
    per instance rather than relied on globally, because a queued mailable is
    rendered by a worker whose locale is not the sender's.
--}}
@props(['value'])

{{ __('app.emails.date_at_time', [
    'date' => $value->locale(app()->getLocale())->translatedFormat(__('app.emails.date_format')),
    'time' => $value->locale(app()->getLocale())->translatedFormat(__('app.emails.time_format')),
]) }}
