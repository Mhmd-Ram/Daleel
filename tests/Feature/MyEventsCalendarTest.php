<?php

use App\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Pull one day out of the calendar grid so placement can be asserted directly
 * rather than by scraping rendered HTML.
 *
 * @param  Collection<int, Collection<int, array>>  $weeks
 * @return array{date: CarbonImmutable, inMonth: bool, isToday: bool, events: Collection}
 */
function dayCell(Collection $weeks, string $date): array
{
    $day = $weeks->collapse()->first(
        fn (array $candidate) => $candidate['date']->toDateString() === $date
    );

    expect($day)->not->toBeNull("expected {$date} to be on the calendar grid");

    return $day;
}

function registerFor(User $user, Event $event): void
{
    $user->registrations()->attach($event, ['created_at' => now()]);
}

it('shows the current month by default', function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-15 10:00'));

    $this->actingAs(User::factory()->create())
        ->get(route('my-events'))
        ->assertOk()
        ->assertSee('August 2026');
});

it('plots a registered event on its start date', function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-01 10:00'));
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'name' => 'Tripoli Design Week',
        'start_date_time' => '2026-08-20 18:00',
        'end_date_time' => '2026-08-20 21:00',
    ]);
    registerFor($user, $event);

    $response = $this->actingAs($user)->get(route('my-events'))->assertOk();
    $weeks = $response->viewData('weeks');

    expect(dayCell($weeks, '2026-08-20')['events']->pluck('id'))->toContain($event->id)
        ->and(dayCell($weeks, '2026-08-19')['events'])->toBeEmpty()
        ->and(dayCell($weeks, '2026-08-21')['events'])->toBeEmpty();

    $response->assertSee('Tripoli Design Week');
});

it('spans a multi-day event across every day it covers', function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-01 10:00'));
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'start_date_time' => '2026-08-10 09:00',
        'end_date_time' => '2026-08-12 17:00',
    ]);
    registerFor($user, $event);

    $weeks = $this->actingAs($user)->get(route('my-events'))->viewData('weeks');

    foreach (['2026-08-10', '2026-08-11', '2026-08-12'] as $covered) {
        expect(dayCell($weeks, $covered)['events']->pluck('id'))->toContain($event->id);
    }

    expect(dayCell($weeks, '2026-08-09')['events'])->toBeEmpty()
        ->and(dayCell($weeks, '2026-08-13')['events'])->toBeEmpty();
});

it('clamps an event that starts before the visible grid', function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-15 10:00'));
    $user = User::factory()->create();
    // Runs from mid-July into early August, so only its August tail is on screen.
    $event = Event::factory()->create([
        'start_date_time' => '2026-07-15 09:00',
        'end_date_time' => '2026-08-03 17:00',
    ]);
    registerFor($user, $event);

    $weeks = $this->actingAs($user)->get(route('my-events'))->viewData('weeks');

    expect(dayCell($weeks, '2026-08-03')['events']->pluck('id'))->toContain($event->id)
        ->and(dayCell($weeks, '2026-08-04')['events'])->toBeEmpty();
});

it('leaves out events the user has not registered for', function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-01 10:00'));
    $user = User::factory()->create();
    Event::factory()->create([
        'name' => 'Someone Elses Meetup',
        'start_date_time' => '2026-08-14 18:00',
        'end_date_time' => '2026-08-14 20:00',
    ]);

    $response = $this->actingAs($user)->get(route('my-events'))->assertOk();

    expect(dayCell($response->viewData('weeks'), '2026-08-14')['events'])->toBeEmpty();
    $response->assertDontSee('Someone Elses Meetup');
});

it('moves to another month through the query string', function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-15 10:00'));
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('my-events', ['month' => '2026-09']))
        ->assertOk()->assertSee('September 2026');

    $this->actingAs($user)->get(route('my-events', ['month' => '2025-12']))
        ->assertOk()->assertSee('December 2025');
});

it('offers previous and next month links that cross the year boundary', function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-15 10:00'));
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('my-events', ['month' => '2026-01']))->assertOk();

    expect($response->viewData('previousMonth'))->toBe('2025-12')
        ->and($response->viewData('nextMonth'))->toBe('2026-02');
});

it('falls back to the current month when the month parameter is unusable', function (string $unusable) {
    $this->travelTo(CarbonImmutable::parse('2026-08-15 10:00'));

    $this->actingAs(User::factory()->create())
        ->get(route('my-events', ['month' => $unusable]))
        ->assertOk()
        ->assertSee('August 2026');
})->with(['banana', '2026-13', '2026-00', '2026-8', '2026/08', '', '99999-01']);

it('falls back to the current month when the month parameter is an array', function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-15 10:00'));

    $this->actingAs(User::factory()->create())
        ->get(route('my-events').'?month[]=2026-09')
        ->assertOk()
        ->assertSee('August 2026');
});

it('shows an empty but valid calendar to a user with no registrations', function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-15 10:00'));

    $response = $this->actingAs(User::factory()->create())
        ->get(route('my-events'))
        ->assertOk()
        ->assertSee('August 2026')
        // Literal template text, so the needle must not be HTML-escaped.
        ->assertSee("You haven't saved anything yet", false);

    $days = $response->viewData('weeks')->collapse();

    expect($days)->not->toBeEmpty()
        ->and($days->every(fn (array $day) => $day['events']->isEmpty()))->toBeTrue();
});

it('always renders whole Sunday to Saturday weeks', function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-15 10:00'));
    $user = User::factory()->create();

    // February 2026 starts on a Sunday, which is the case most likely to produce
    // an off-by-one leading pad; May 2026 ends on a Sunday.
    foreach (['2026-02', '2026-05', '2026-08', '2024-02'] as $month) {
        $weeks = $this->actingAs($user)->get(route('my-events', ['month' => $month]))->viewData('weeks');
        $days = $weeks->collapse();

        expect($weeks->every(fn (Collection $week) => $week->count() === 7))->toBeTrue()
            ->and($days->first()['date']->dayOfWeek)->toBe(CarbonImmutable::SUNDAY)
            ->and($days->last()['date']->dayOfWeek)->toBe(CarbonImmutable::SATURDAY);
    }
});

it('marks which grid days belong to the month on screen', function () {
    $this->travelTo(CarbonImmutable::parse('2026-08-15 10:00'));

    $weeks = $this->actingAs(User::factory()->create())
        ->get(route('my-events', ['month' => '2026-08']))
        ->viewData('weeks');

    expect(dayCell($weeks, '2026-08-01')['inMonth'])->toBeTrue()
        ->and(dayCell($weeks, '2026-08-31')['inMonth'])->toBeTrue()
        ->and(dayCell($weeks, '2026-07-31')['inMonth'])->toBeFalse()
        ->and(dayCell($weeks, '2026-09-01')['inMonth'])->toBeFalse();
});

it('keeps the calendar behind auth', function () {
    $this->get(route('my-events'))->assertRedirect(route('login'));
});
