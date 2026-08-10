<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GeocodeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Organizer;
use App\Http\Controllers\OrganizerApplicationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');

/*
|--------------------------------------------------------------------------
| Address lookup for the event form's map picker
|--------------------------------------------------------------------------
| Sits outside the groups below because the event form is reached from both
| sides: organizers on the web guard, admins on the admin guard. Kept behind
| authentication and throttled so it cannot be used as an open geocoding proxy.
*/
Route::get('/geocode', GeocodeController::class)
    ->middleware(['auth:web,admin', 'throttle:20,1'])
    ->name('geocode');

/*
|--------------------------------------------------------------------------
| Guest (user) authentication
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Authenticated users
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Email verification. These have to stay reachable while unverified.
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verify/resend', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')->name('verification.send');

    Route::delete('/events/{event}/register', [RegistrationController::class, 'destroy'])->name('events.cancel');
    Route::get('/my-events', [RegistrationController::class, 'index'])->name('my-events');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    /*
    | Actions that need a confirmed email address.
    */
    Route::middleware('verified')->group(function () {
        Route::post('/events/{event}/register', [RegistrationController::class, 'store'])->name('events.register');
        Route::post('/organizer/apply', [OrganizerApplicationController::class, 'store'])->name('organizer.apply');
    });

    /*
    | Organizer area — approved organizers only.
    */
    Route::middleware(['verified', 'organizer'])->prefix('organizer')->name('organizer.')->group(function () {
        Route::resource('events', Organizer\EventController::class)->except('show');
    });
});

/*
|--------------------------------------------------------------------------
| Admin (separate "admin" guard)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [Admin\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [Admin\Auth\AuthenticatedSessionController::class, 'store']);
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [Admin\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::resource('categories', Admin\CategoryController::class)->except('show');
        Route::resource('events', Admin\EventController::class)->except('show');
        Route::patch('/events/{event}/publish', [Admin\EventController::class, 'togglePublish'])->name('events.publish');
        Route::get('/events/{event}/registrations', [Admin\EventController::class, 'registrations'])->name('events.registrations');

        Route::get('/organizer-applications', [Admin\OrganizerApplicationController::class, 'index'])
            ->name('organizer-applications.index');
        Route::patch('/organizer-applications/{application}/approve', [Admin\OrganizerApplicationController::class, 'approve'])
            ->name('organizer-applications.approve');
        Route::patch('/organizer-applications/{application}/reject', [Admin\OrganizerApplicationController::class, 'reject'])
            ->name('organizer-applications.reject');
    });
});
