<?php

use App\Http\Middleware\EnsureUserIsNotBanned;
use App\Http\Middleware\EnsureUserIsOrganizer;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Send unauthenticated visitors to the right login screen: admin area
        // routes go to the admin login, everything else to the user login.
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->is('admin', 'admin/*')
                ? route('admin.login')
                : route('login');
        });

        // Appended, not prepended: the locale lives in the session, and
        // prepending would run this before StartSession, where
        // $request->session() throws. Appending still leaves it ahead of the
        // route action, so views and validation see the right locale.
        $middleware->web(append: [SetLocale::class]);

        $middleware->alias([
            'not-banned' => EnsureUserIsNotBanned::class,
            'organizer' => EnsureUserIsOrganizer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
