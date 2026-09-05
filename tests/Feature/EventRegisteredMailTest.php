<?php

use App\Mail\EventRegistered;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Carbon;
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

it('embeds the logo instead of linking to it', function () {
    $rendered = (new EventRegistered(User::factory()->create(), Event::factory()->create()))->render();

    // A linked asset() URL points at this host, which a mail client cannot
    // reach from a reader's inbox. render() rewrites the CID to inline data for
    // browser preview, so either form proves the file travels with the message.
    expect($rendered)
        ->not->toContain('wordmark-light.png')
        ->toContain('data:image/png;base64,');
});

it('writes dates and currency in the reader language', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'start_date_time' => Carbon::parse('2026-09-11 20:30'),
        'end_date_time' => Carbon::parse('2026-09-11 23:00'),
        'tiket_cost' => 25.00,
    ]);

    $english = (new EventRegistered($user, $event))->locale('en')->render();
    $arabic = (new EventRegistered($user, $event))->locale('ar')->render();

    expect($english)
        ->toContain('Friday, Sep 11, 2026 at 8:30 PM')
        ->toContain('25.00 LYD');

    // The whole point: no English day names, no "at", no Latin currency code
    // sitting inside an otherwise Arabic message.
    expect($arabic)
        ->toContain('الجمعة')
        ->toContain('سبتمبر')
        ->toContain('د.ل')
        ->not->toContain('Friday')
        ->not->toContain('LYD');
});
