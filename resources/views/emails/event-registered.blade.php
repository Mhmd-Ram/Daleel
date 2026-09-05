<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.emails.subject_saved', ['event' => $event->name]) }}</title>
</head>
<body style="margin:0; padding:24px; background-color:#fafaf9; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#1c1917;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:560px; margin:0 auto; border-collapse:collapse;">
        <tr>
            <td style="padding:24px 28px; background-color:#a82f31; border-radius:16px 16px 0 0;">
                {{-- Embedded rather than linked. `asset()` yields an absolute URL to
                     this host, which a mail client cannot reach when the app runs on
                     localhost - and even in production most clients block remote
                     images until the reader allows them. A CID attachment always
                     renders. --}}
                <img src="{{ $message->embed(public_path('wordmark-light.png')) }}" alt="{{ config('app.name') }}"
                     width="120" height="41" style="display:block; border:0; height:auto; max-width:120px;">
                <p style="margin:8px 0 0; font-size:22px; font-weight:700; color:#ffffff;">
                    {{ __('app.emails.saved_heading') }}
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:28px; background-color:#ffffff; border:1px solid #e7e5e4; border-top:0; border-radius:0 0 16px 16px;">
                <p style="margin:0 0 20px; font-size:15px; line-height:1.6; color:#44403c;">
                    {!! __('app.emails.saved_body', [
                        'name' => e($user->name),
                        'event' => '<strong style="color:#1c1917;">'.e($event->name).'</strong>',
                    ]) !!}
                </p>

                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse; font-size:14px;">
                    <tr>
                        <td style="padding:10px 0; color:#78716c; width:110px;">{{ __('app.emails.starts') }}</td>
                        <td style="padding:10px 0; font-weight:600; color:#1c1917;"><x-mail.datetime :value="$event->start_date_time" /></td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; border-top:1px solid #f5f5f4; color:#78716c;">{{ __('app.emails.ends') }}</td>
                        <td style="padding:10px 0; border-top:1px solid #f5f5f4; font-weight:600; color:#1c1917;"><x-mail.datetime :value="$event->end_date_time" /></td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; border-top:1px solid #f5f5f4; color:#78716c;">{{ __('app.emails.location') }}</td>
                        <td style="padding:10px 0; border-top:1px solid #f5f5f4; font-weight:600; color:#1c1917;">{{ $event->location }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; border-top:1px solid #f5f5f4; color:#78716c;">{{ __('app.emails.ticket') }}</td>
                        <td style="padding:10px 0; border-top:1px solid #f5f5f4; font-weight:600; color:#1c1917;">
                            {{ (float) $event->tiket_cost > 0
                                ? __('app.emails.ticket_price', ['amount' => number_format((float) $event->tiket_cost, 2)])
                                : __('app.common.free') }}
                        </td>
                    </tr>
                </table>

                <p style="margin:28px 0 0;">
                    <a href="{{ route('events.show', $event) }}"
                       style="display:inline-block; padding:12px 20px; background-color:#a82f31; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none; border-radius:8px;">
                        {{ __('app.emails.view_the_event') }}
                    </a>
                </p>

                <p style="margin:24px 0 0; font-size:13px; line-height:1.6; color:#78716c;">
                    {{ __('app.emails.cannot_make_it') }}
                    <a href="{{ route('my-events') }}" style="color:#8a2228;">{{ __('app.emails.my_calendar') }}</a>.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
