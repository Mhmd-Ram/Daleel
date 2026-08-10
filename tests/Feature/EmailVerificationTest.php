<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\URL;

it('blocks an unverified user from registering for an event', function () {
    $user = User::factory()->unverified()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->post(route('events.register', $event))
        ->assertRedirect(route('verification.notice'));

    expect($event->registeredUsers()->count())->toBe(0);
});

it('blocks an unverified user from applying to become an organizer', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post(route('organizer.apply'), ['message' => str_repeat('I want to run meetups. ', 3)])
        ->assertRedirect(route('verification.notice'));

    expect($user->organizerApplications()->count())->toBe(0);
});

it('shows the verification notice to an unverified user', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('verification.notice'))
        ->assertOk()
        ->assertSee('Verify your email');
});

it('sends a verified user away from the notice page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('verification.notice'))
        ->assertRedirect(route('home'));
});

it('marks the account verified when the signed link is opened', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->actingAs($user)->get($url)->assertRedirect(route('home'));

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
});

it('does not verify the account when the link signature is wrong', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1('someone-elses@example.com'),
    ]);

    $this->actingAs($user)->get($url)->assertForbidden();

    expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
});

it('lets a verified user register for an event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)->post(route('events.register', $event));

    expect($event->registeredUsers()->whereKey($user->id)->exists())->toBeTrue();
});
