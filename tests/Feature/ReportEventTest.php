<?php

use App\Enums\ReportReason;
use App\Models\Event;
use App\Models\Report;
use App\Models\User;

it('lets a verified user report an event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->from(route('events.show', $event))
        ->post(route('events.report', $event), ['reason' => ReportReason::Spam->value])
        ->assertRedirect(route('events.show', $event))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('reports', [
        'attendee_id' => $user->id,
        'event_id' => $event->id,
        'reason' => ReportReason::Spam->value,
    ]);
});

it('blocks an unverified user from reporting', function () {
    $user = User::factory()->unverified()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->post(route('events.report', $event), ['reason' => ReportReason::Spam->value])
        ->assertRedirect(route('verification.notice'));

    $this->assertDatabaseCount('reports', 0);
});

it('requires a guest to log in before reporting', function () {
    $event = Event::factory()->create();

    $this->post(route('events.report', $event), ['reason' => ReportReason::Spam->value])
        ->assertRedirect(route('login'));

    $this->assertDatabaseCount('reports', 0);
});

it('rejects a reason that is not on the list', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->post(route('events.report', $event), ['reason' => 'i_just_dont_like_it'])
        ->assertSessionHasErrors('reason');

    $this->assertDatabaseCount('reports', 0);
});

it('refuses a second report from the same person for the same event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->from(route('events.show', $event))
        ->post(route('events.report', $event), ['reason' => ReportReason::Spam->value]);

    $this->actingAs($user)
        ->from(route('events.show', $event))
        ->post(route('events.report', $event), ['reason' => ReportReason::Other->value])
        ->assertRedirect(route('events.show', $event))
        ->assertSessionHas('error');

    expect(Report::where('event_id', $event->id)->count())->toBe(1);
});

it('lets two different people report the same event', function () {
    $event = Event::factory()->create();

    foreach ([User::factory()->create(), User::factory()->create()] as $reporter) {
        $this->actingAs($reporter)
            ->from(route('events.show', $event))
            ->post(route('events.report', $event), ['reason' => ReportReason::Spam->value]);
    }

    expect(Report::where('event_id', $event->id)->count())->toBe(2);
});

it('does not report an unpublished event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->inactive()->create();

    $this->actingAs($user)
        ->post(route('events.report', $event), ['reason' => ReportReason::Spam->value])
        ->assertNotFound();

    $this->assertDatabaseCount('reports', 0);
});

it('does not show the report form to guests', function () {
    $event = Event::factory()->create();

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertDontSee('Report this event');
});

it('shows the report form to a signed-in user', function () {
    $event = Event::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('events.show', $event))
        ->assertOk()
        ->assertSee('Report this event');
});

it('deletes the reports when the event is deleted', function () {
    $report = Report::factory()->create();

    $report->event->delete();

    $this->assertDatabaseMissing('reports', ['id' => $report->id]);
});

it('deletes the reports when the reporter deletes their account', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->from(route('events.show', $event))
        ->post(route('events.report', $event), ['reason' => ReportReason::Spam->value]);

    $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password']);

    $this->assertDatabaseCount('reports', 0);
    // The event is admin-owned, so it outlives the reporter.
    $this->assertDatabaseHas('events', ['id' => $event->id]);
});
