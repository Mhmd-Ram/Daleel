<?php

use App\Models\User;

it('refuses to log a banned user in', function () {
    $user = User::factory()->banned()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('tells a banned user why they were refused', function () {
    $user = User::factory()->banned()->create();

    // assertInvalid matches on substring, so this pins the explanation without
    // freezing the exact wording.
    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertInvalid(['email' => 'banned']);
});

it('does not reveal a ban when the password is wrong', function () {
    $user = User::factory()->banned()->create();

    // The generic message, not the ban message: otherwise the login screen
    // becomes a way to discover which email addresses have accounts.
    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'not-the-password',
    ])->assertInvalid(['email' => 'These credentials do not match our records.']);
});

it('logs out a user who is banned mid-session', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('home'));

    // Banned straight in the database, bypassing the signed-in model instance.
    User::where('id', $user->id)->update(['is_banned' => true]);

    // A real second request boots a fresh container and re-resolves the user
    // from the session. In a test the container is shared and SessionGuard
    // memoises whoever it already resolved, so drop the guards to reproduce
    // that boundary instead of asserting against a stale instance.
    $this->app['auth']->forgetGuards();

    $this->get(route('profile.show'))->assertRedirect(route('login'));

    $this->assertGuest();
});

it('still lets an unbanned user sign in', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

it('lets a user whose ban was lifted sign in again', function () {
    $user = User::factory()->banned()->create();

    $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);
    $this->assertGuest();

    User::where('id', $user->id)->update(['is_banned' => false]);

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

it('explains the ban on the login page after evicting a session', function () {
    $user = User::factory()->create();

    $this->post(route('login'), ['email' => $user->email, 'password' => 'password']);

    User::where('id', $user->id)->update(['is_banned' => true]);
    $this->app['auth']->forgetGuards();

    // Following the redirect proves the flashed reason actually reaches the
    // login page, not just that the eviction happened.
    $this->followingRedirects()
        ->get(route('profile.show'))
        ->assertOk()
        ->assertSee('This account has been banned.');
});
