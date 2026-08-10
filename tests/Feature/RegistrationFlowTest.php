<?php

use App\Models\Event;
use App\Models\User;

it('lets a logged-in user register for an active upcoming event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->post(route('events.register', $event))
        ->assertRedirect();

    expect($event->registeredUsers()->whereKey($user->id)->exists())->toBeTrue();
});

it('prevents a user from registering for the same event twice', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)->post(route('events.register', $event));
    $this->actingAs($user)->post(route('events.register', $event));

    expect($event->registeredUsers()->whereKey($user->id)->count())->toBe(1);
});

it('blocks registration for events that have already finished', function () {
    $user = User::factory()->create();
    $event = Event::factory()->past()->create();

    $this->actingAs($user)->post(route('events.register', $event));

    expect($event->registeredUsers()->count())->toBe(0);
});

it('blocks registration for inactive events', function () {
    $user = User::factory()->create();
    $event = Event::factory()->inactive()->create();

    $this->actingAs($user)->post(route('events.register', $event));

    expect($event->registeredUsers()->count())->toBe(0);
});

it('blocks registration once max capacity is reached', function () {
    $event = Event::factory()->create(['max_capacity' => 1]);
    User::factory()->create()->registrations()->attach($event, ['created_at' => now()]);

    $late = User::factory()->create();
    $this->actingAs($late)->post(route('events.register', $event));

    expect($event->registeredUsers()->count())->toBe(1);
});

it('lets a user cancel a registration', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $user->registrations()->attach($event, ['created_at' => now()]);

    $this->actingAs($user)
        ->delete(route('events.cancel', $event))
        ->assertRedirect();

    expect($event->registeredUsers()->count())->toBe(0);
});

it('requires a guest to log in before registering', function () {
    $event = Event::factory()->create();

    $this->post(route('events.register', $event))->assertRedirect(route('login'));
    expect($event->registeredUsers()->count())->toBe(0);
});
