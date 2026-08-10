<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Show the user sign-up form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Store a new user, log them in, and send them off to verify their email.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        // Laravel's SendEmailVerificationNotification listener picks this up.
        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('verification.notice')
            ->with('success', 'Welcome, '.$user->name.'! Check your inbox to verify your email.');
    }
}
