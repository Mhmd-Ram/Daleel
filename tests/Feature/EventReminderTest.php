<?php

use App\Mail\EventReminder;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * Save an event to someone's calendar, the way the app does.
 */
function saveToCalendar(Event $event, User $user): void
{
    $event->registeredUsers()->attach($user->id, ['created_at' => now()]);
}

beforeEach(function () {
    Mail::fake();
    $this->travelTo(Carbon::parse('2026-11-15 09:00:00'));
});

it('queues a reminder for an event starting inside the window', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'start_date_time' => '2026-11-15 18:00:00',
        'end_date_time' => '2026-11-15 21:00:00',
    ]);
    saveToCalendar($event, $user);

    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertQueued(EventReminder::class, fn ($mail) => $mail->hasTo($user->email)
        && $mail->event->is($event));
});

it('does not remind about an event further out than the window', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'start_date_time' => '2026-11-19 18:00:00',
        'end_date_time' => '2026-11-19 21:00:00',
    ]);
    saveToCalendar($event, $user);

    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertNothingQueued();
});

it('does not remind about an event that already started', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'start_date_time' => '2026-11-15 08:00:00',
        'end_date_time' => '2026-11-15 23:00:00',
    ]);
    saveToCalendar($event, $user);

    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertNothingQueued();
});

it('does not remind about an unpublished event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->inactive()->create([
        'start_date_time' => '2026-11-15 18:00:00',
        'end_date_time' => '2026-11-15 21:00:00',
    ]);
    saveToCalendar($event, $user);

    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertNothingQueued();
});

it('marks the calendar entry so the reminder is never sent twice', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'start_date_time' => '2026-11-15 18:00:00',
        'end_date_time' => '2026-11-15 21:00:00',
    ]);
    saveToCalendar($event, $user);

    $this->artisan('events:send-reminders')->assertSuccessful();
    $this->artisan('events:send-reminders')->assertSuccessful();

    // The whole point of the flag: an hourly job must not nag.
    Mail::assertQueuedCount(1);

    $this->assertDatabaseHas('user_regestrations', [
        'user_id' => $user->id,
        'event_id' => $event->id,
        'reminder_sent' => true,
    ]);
});

it('does not remind someone who removed the event from their calendar', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'start_date_time' => '2026-11-15 18:00:00',
        'end_date_time' => '2026-11-15 21:00:00',
    ]);
    saveToCalendar($event, $user);
    $event->registeredUsers()->detach($user->id);

    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertNothingQueued();
});

it('reminds every person who saved the same event', function () {
    $event = Event::factory()->create([
        'start_date_time' => '2026-11-15 18:00:00',
        'end_date_time' => '2026-11-15 21:00:00',
    ]);

    $savers = User::factory()->count(3)->create();
    $savers->each(fn (User $user) => saveToCalendar($event, $user));

    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertQueuedCount(3);
    $savers->each(fn (User $user) => Mail::assertQueued(
        EventReminder::class,
        fn ($mail) => $mail->hasTo($user->email),
    ));
});

it('reminds about one saved event without touching another that is out of range', function () {
    $user = User::factory()->create();

    $soon = Event::factory()->create([
        'name' => 'Tomorrow Morning Run',
        'start_date_time' => '2026-11-15 18:00:00',
        'end_date_time' => '2026-11-15 21:00:00',
    ]);
    $later = Event::factory()->create([
        'name' => 'Next Week Conference',
        'start_date_time' => '2026-11-22 18:00:00',
        'end_date_time' => '2026-11-22 21:00:00',
    ]);

    saveToCalendar($soon, $user);
    saveToCalendar($later, $user);

    $this->artisan('events:send-reminders')->assertSuccessful();

    Mail::assertQueuedCount(1);
    Mail::assertQueued(EventReminder::class, fn ($mail) => $mail->event->is($soon));

    // The out-of-range entry stays unflagged, so it can still be reminded later.
    $this->assertDatabaseHas('user_regestrations', [
        'user_id' => $user->id,
        'event_id' => $later->id,
        'reminder_sent' => false,
    ]);
});

it('renders the reminder email with the event name and start time', function () {
    $user = User::factory()->create(['name' => 'Salma Drissi']);
    $event = Event::factory()->create([
        'name' => 'Harbour Night Market',
        'start_date_time' => '2026-11-15 18:00:00',
        'end_date_time' => '2026-11-15 21:00:00',
    ]);

    $rendered = (new EventReminder($user, $event))->render();

    expect($rendered)
        ->toContain('Harbour Night Market')
        ->toContain('Salma Drissi')
        ->toContain('Coming up soon');
});
