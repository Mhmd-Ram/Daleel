<?php

use App\Mail\EventRegistered;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('queues a confirmation email when a user registers for an event', function () {
    Mail::fake();

    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)->post(route('events.register', $event));

    Mail::assertQueued(
        EventRegistered::class,
        fn (EventRegistered $mail) => $mail->hasTo($user->email) && $mail->event->is($event),
    );
});

it('does not email anyone when the registration is rejected', function () {
    Mail::fake();

    $user = User::factory()->create();
    $event = Event::factory()->past()->create();

    $this->actingAs($user)->post(route('events.register', $event));

    Mail::assertNothingQueued();
});

it('renders the confirmation email with the event details', function () {
    $user = User::factory()->create(['name' => 'Salma Rekik']);
    $event = Event::factory()->create(['name' => 'Tripoli Tech Night', 'location' => 'Old City Hall']);

    $rendered = (new EventRegistered($user, $event))->render();

    expect($rendered)
        ->toContain('Salma Rekik')
        ->toContain('Tripoli Tech Night')
        ->toContain('Old City Hall');
});
