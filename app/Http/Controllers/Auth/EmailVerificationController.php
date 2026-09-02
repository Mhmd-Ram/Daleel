<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailVerificationController extends Controller
{
    /**
     * Tell a signed-in but unverified user to check their inbox.
     */
    public function notice(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route('home')
            : view('auth.verify-email');
    }

    /**
     * Mark the account verified from the signed link in the email.
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home')->with('success', __('app.flash.already_verified'));
        }

        $request->fulfill();

        return redirect()->route('home')->with('success', __('app.flash.email_verified'));
    }

    /**
     * Send a fresh verification link.
     *
     * A transport failure is reported rather than swallowed: telling someone the
     * link is on its way when SMTP just refused it leaves them waiting on an
     * email that will never arrive, with no way to tell the difference.
     */
    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('home');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (TransportExceptionInterface $e) {
            report($e);

            return back()->with('error', __('app.flash.verification_failed'));
        }

        return back()->with('success', __('app.flash.verification_sent'));
    }
}
