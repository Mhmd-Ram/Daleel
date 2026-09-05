<?php

use App\Enums\ReportReason;
use App\Models\Admin;
use App\Models\Event;
use App\Models\Report;
use App\Models\User;

it('keeps the report queue behind the admin guard', function () {
    $this->get(route('admin.reports.index'))->assertRedirect(route('admin.login'));

    $this->actingAs(User::factory()->create())
        ->get(route('admin.reports.index'))
        ->assertRedirect(route('admin.login'));
});

it('lists reported events with the reason and the reporter', function () {
    $event = Event::factory()->create(['name' => 'Harbour Night Market']);
    $reporter = User::factory()->create(['name' => 'Salma Drissi']);

    Report::factory()->create([
        'event_id' => $event->id,
        'attendee_id' => $reporter->id,
        'reason' => ReportReason::FakeLocation,
    ]);

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.reports.index'))
        ->assertOk()
        ->assertSee('Harbour Night Market')
        ->assertSee('Salma Drissi')
        ->assertSee('Fake or wrong location');
});

it('shows the empty state when nothing has been reported', function () {
    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.reports.index'))
        ->assertOk()
        ->assertSee('Nothing reported');
});

it('ranks the most reported events first', function () {
    $busy = Event::factory()->create(['name' => 'Harbour Night Market']);
    $quiet = Event::factory()->create(['name' => 'Quiet Book Club']);

    Report::factory()->count(3)->create(['event_id' => $busy->id]);
    Report::factory()->create(['event_id' => $quiet->id]);

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.reports.index'))
        ->assertOk()
        ->assertSeeInOrder(['Most reported', 'Harbour Night Market', 'Quiet Book Club']);
});

it('leaves an unreported event out of the most reported list', function () {
    Event::factory()->create(['name' => 'Never Flagged Gig']);
    Report::factory()->create();

    $response = $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.reports.index'))
        ->assertOk();

    // It has no reports, so it must not appear in the ranking at all.
    $response->assertDontSee('Never Flagged Gig');
});

it('dismisses a report', function () {
    $report = Report::factory()->create();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->from(route('admin.reports.index'))
        ->delete(route('admin.reports.destroy', $report))
        ->assertRedirect(route('admin.reports.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('reports', ['id' => $report->id]);
});

it('removes the reports when the admin deletes the event', function () {
    $admin = Admin::factory()->create();
    $event = Event::factory()->for($admin)->create();
    $report = Report::factory()->create(['event_id' => $event->id]);

    $this->actingAs($admin, 'admin')->delete(route('admin.events.destroy', $event));

    $this->assertDatabaseMissing('reports', ['id' => $report->id]);
});

it('counts reports on the admin events list', function () {
    $admin = Admin::factory()->create();
    $event = Event::factory()->for($admin)->create(['name' => 'Harbour Night Market']);
    Report::factory()->count(2)->create(['event_id' => $event->id]);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.events.index'))
        ->assertOk()
        ->assertSeeInOrder(['Harbour Night Market', '2 reports']);
});

it('surfaces waiting reports on the dashboard', function () {
    Report::factory()->count(2)->create();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('reports waiting for review');
});

it('narrows the queue to one event when the badge is followed', function () {
    $flagged = Event::factory()->create(['name' => 'Harbour Night Market']);
    $other = Event::factory()->create(['name' => 'Desert Film Club']);

    Report::factory()->create(['event_id' => $flagged->id]);
    Report::factory()->create(['event_id' => $other->id]);

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.reports.index', ['event' => $flagged->id]))
        ->assertOk()
        ->assertSee('Harbour Night Market')
        ->assertDontSee('Desert Film Club');
});

it('keeps the event filter on the paging links', function () {
    $event = Event::factory()->create();
    Report::factory()->count(30)->create(['event_id' => $event->id]);

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.reports.index', ['event' => $event->id]))
        ->assertOk()
        ->assertSee('event='.$event->id, escape: false);
});

it('ignores an unknown event filter rather than failing', function () {
    Report::factory()->create();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.reports.index', ['event' => 99999]))
        ->assertOk();
});
