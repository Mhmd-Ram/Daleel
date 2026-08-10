<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

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
     *
     * The sign-in happens before the email goes out, and a transport failure is
     * reported rather than swallowed. The account already exists by that point,
     * so letting a refused SMTP connection bubble up would strand someone with a
     * real account, no session, and a 500 page. They land on the notice page
     * either way; only the message differs.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        Auth::login($user);

        try {
            // Laravel's SendEmailVerificationNotification listener picks this up.
            event(new Registered($user));
        } catch (TransportExceptionInterface $e) {
            report($e);

            return redirect()->route('verification.notice')
                ->with('error', 'Your account is ready, but we could not send the verification email. Use the resend button below.');
        }

        return redirect()->route('verification.notice')
            ->with('success', 'Welcome, '.$user->name.'! Check your inbox to verify your email.');
    }
}
