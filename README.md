<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Background processes

Two long-running processes are required in production. Without them the
features that depend on them are only half-shipped.

**Scheduler.** The event reminder email (`events:send-reminders`) is scheduled
hourly. Nothing runs it unless the scheduler is running:

```
* * * * * cd /path-to-daleel && php artisan schedule:run >> /dev/null 2>&1
```

**Queue worker.** Both mailables are `ShouldQueue`, so confirmation and
reminder emails sit in the queue until a worker picks them up:

```
php artisan queue:work --tries=3
```

The reminder command is idempotent: a `reminder_sent` flag on each calendar
entry means an extra run, or a run after a failed one, never sends a duplicate.

## Email

Locally the app points at [Mailpit](https://mailpit.axllent.org/), a local SMTP
sink with a web inbox at `http://localhost:8025`. Nothing leaves the machine, so
a fresh checkout cannot email real people.

For real delivery over Gmail, swap in the commented block in `.env.example` and
set `MAIL_PASSWORD` to a Google [App Password](https://myaccount.google.com/apppasswords)
(16 characters, spaces removed). It requires 2-Step Verification and is *not*
your normal Google password. Two things Gmail is strict about:

- Port **587** goes with `MAIL_SCHEME=smtp` (STARTTLS). `smtps` is port 465, and
  the wrong pairing hangs rather than reporting an error.
- `MAIL_FROM_ADDRESS` must be the authenticated account or a verified alias.
  Gmail rewrites or rejects any other sender.

Check the configuration end to end:

```
php artisan mail:test you@example.com
```

It sends synchronously and prints the transport's own error, which is what tells
a bad app password apart from a queue worker that is not running.

**If mail seems to vanish, check the queue worker first.** The registration
confirmation and the reminder are both `ShouldQueue`, so with SMTP configured
perfectly and no worker running they sit in the `jobs` table and nothing is
delivered. The verification email is queued too.

In production prefer `MAIL_MAILER=failover`, already configured as smtp then
log: an outage then degrades to a log line instead of throwing inside the worker.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
