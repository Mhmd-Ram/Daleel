<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.emails.subject_reminder', ['event' => $event->name]) }}</title>
</head>
<body style="margin:0; padding:24px; background-color:#fafaf9; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#1c1917;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:560px; margin:0 auto; border-collapse:collapse;">
        <tr>
            <td style="padding:24px 28px; background-color:#059669; border-radius:16px 16px 0 0;">
                <p style="margin:0; font-size:13px; font-weight:600; letter-spacing:0.16em; text-transform:uppercase; color:rgba(255,255,255,0.85);">
                    {{ config('app.name') }}
                </p>
                <p style="margin:8px 0 0; font-size:22px; font-weight:700; color:#ffffff;">
                    {{ __('app.emails.reminder_heading') }}
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:28px; background-color:#ffffff; border:1px solid #e7e5e4; border-top:0; border-radius:0 0 16px 16px;">
                <p style="margin:0 0 20px; font-size:15px; line-height:1.6; color:#44403c;">
                    {!! __('app.emails.reminder_body', [
                        'name' => e($user->name),
                        'event' => '<strong style="color:#1c1917;">'.e($event->name).'</strong>',
                    ]) !!}
                </p>

                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse; font-size:14px;">
                    <tr>
                        <td style="padding:10px 0; color:#78716c; width:110px;">{{ __('app.emails.starts') }}</td>
                        <td style="padding:10px 0; font-weight:600; color:#1c1917;">{{ $event->start_date_time->format('l, M j, Y \a\t g:i A') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; border-top:1px solid #f5f5f4; color:#78716c;">{{ __('app.emails.ends') }}</td>
                        <td style="padding:10px 0; border-top:1px solid #f5f5f4; font-weight:600; color:#1c1917;">{{ $event->end_date_time->format('l, M j, Y \a\t g:i A') }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; border-top:1px solid #f5f5f4; color:#78716c;">{{ __('app.emails.location') }}</td>
                        <td style="padding:10px 0; border-top:1px solid #f5f5f4; font-weight:600; color:#1c1917;">{{ $event->location }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; border-top:1px solid #f5f5f4; color:#78716c;">{{ __('app.emails.ticket') }}</td>
                        <td style="padding:10px 0; border-top:1px solid #f5f5f4; font-weight:600; color:#1c1917;">
                            {{ (float) $event->tiket_cost > 0 ? number_format((float) $event->tiket_cost, 2).' LYD' : __('app.common.free') }}
                        </td>
                    </tr>
                </table>

                <p style="margin:28px 0 0;">
                    <a href="{{ route('events.show', $event) }}"
                       style="display:inline-block; padding:12px 20px; background-color:#059669; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none; border-radius:8px;">
                        {{ __('app.emails.view_the_event') }}
                    </a>
                </p>

                <p style="margin:24px 0 0; font-size:13px; line-height:1.6; color:#78716c;">
                    {{ __('app.emails.plans_changed') }}
                    <a href="{{ route('my-events') }}" style="color:#047857;">{{ __('app.emails.my_calendar') }}</a>.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
