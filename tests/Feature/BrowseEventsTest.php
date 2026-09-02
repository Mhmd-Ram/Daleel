<?php

use App\Models\Category;
use App\Models\Event;
use Carbon\Carbon;

it('lists active upcoming events on the events page', function () {
    $event = Event::factory()->create(['name' => 'Visible Gig']);

    $this->get(route('events.index'))->assertOk()->assertSee('Visible Gig');
});

it('hides inactive events from the public listing', function () {
    Event::factory()->inactive()->create(['name' => 'Hidden Draft']);

    $this->get(route('events.index'))->assertOk()->assertDontSee('Hidden Draft');
});

it('filters events by category', function () {
    $music = Category::factory()->create(['name' => 'Music']);
    $tech = Category::factory()->create(['name' => 'Tech']);
    Event::factory()->for($music)->create(['name' => 'Jazz Evening']);
    Event::factory()->for($tech)->create(['name' => 'Dev Meetup']);

    $this->get(route('events.index', ['category' => $music->id]))
        ->assertOk()
        ->assertSee('Jazz Evening')
        ->assertDontSee('Dev Meetup');
});

it('returns 404 for an inactive event detail page', function () {
    $event = Event::factory()->inactive()->create();

    // Still 404 for crawlers, but a human now gets an explanation (FR-5.4).
    $this->get(route('events.show', $event))
        ->assertNotFound()
        ->assertSee('Event Unavailable');
});

/*
|--------------------------------------------------------------------------
| Search and filters (FR-4.2 - FR-4.6)
|--------------------------------------------------------------------------
*/

it('finds an event by a word in its title', function () {
    Event::factory()->create(['name' => 'Ceramics Workshop']);
    Event::factory()->create(['name' => 'Startup Pitch Night']);

    $this->get(route('events.index', ['q' => 'Ceramics']))
        ->assertOk()
        ->assertSee('Ceramics Workshop')
        ->assertDontSee('Startup Pitch Night');
});

it('finds an event by a word in its description', function () {
    Event::factory()->create([
        'name' => 'Startup Pitch Night',
        'description' => 'Founders present to investors.',
    ]);
    Event::factory()->create([
        'name' => 'Ceramics Workshop',
        'description' => 'Throwing clay for beginners.',
    ]);

    $this->get(route('events.index', ['q' => 'investors']))
        ->assertOk()
        ->assertSee('Startup Pitch Night')
        ->assertDontSee('Ceramics Workshop');
});

it('matches the keyword regardless of letter case', function () {
    Event::factory()->create(['name' => 'Ceramics Workshop']);

    $this->get(route('events.index', ['q' => 'CERAMICS']))
        ->assertOk()
        ->assertSee('Ceramics Workshop');
});

it('shows a no results message when nothing matches the filters', function () {
    Event::factory()->create(['name' => 'Ceramics Workshop']);

    $this->get(route('events.index', ['q' => 'kitesurfing']))
        ->assertOk()
        ->assertSee('No results found')
        ->assertDontSee('No events here yet');
});

it('shows the plain empty state when there are no events at all', function () {
    $this->get(route('events.index'))
        ->assertOk()
        ->assertSee('No events here yet')
        ->assertDontSee('No results found');
});

it('filters events by city', function () {
    Event::factory()->create(['name' => 'Ceramics Workshop', 'city' => 'Benghazi']);
    Event::factory()->create(['name' => 'Startup Pitch Night', 'city' => 'Misrata']);

    $this->get(route('events.index', ['city' => 'Benghazi']))
        ->assertOk()
        ->assertSee('Ceramics Workshop')
        ->assertDontSee('Startup Pitch Night');
});

it('filters events to today only', function () {
    $this->travelTo(Carbon::parse('2026-11-15 09:00:00'));

    Event::factory()->create([
        'name' => 'Ceramics Workshop',
        'start_date_time' => '2026-11-15 18:00:00',
        'end_date_time' => '2026-11-15 20:00:00',
    ]);
    Event::factory()->create([
        'name' => 'Startup Pitch Night',
        'start_date_time' => '2026-11-20 18:00:00',
        'end_date_time' => '2026-11-20 20:00:00',
    ]);

    $this->get(route('events.index', ['date' => 'today']))
        ->assertOk()
        ->assertSee('Ceramics Workshop')
        ->assertDontSee('Startup Pitch Night');
});

it('filters events to this month', function () {
    $this->travelTo(Carbon::parse('2026-11-15 09:00:00'));

    Event::factory()->create([
        'name' => 'Ceramics Workshop',
        'start_date_time' => '2026-11-20 18:00:00',
        'end_date_time' => '2026-11-20 20:00:00',
    ]);
    Event::factory()->create([
        'name' => 'Startup Pitch Night',
        'start_date_time' => '2026-12-05 18:00:00',
        'end_date_time' => '2026-12-05 20:00:00',
    ]);

    $this->get(route('events.index', ['date' => 'this_month']))
        ->assertOk()
        ->assertSee('Ceramics Workshop')
        ->assertDontSee('Startup Pitch Night');
});

it('filters events to next month', function () {
    $this->travelTo(Carbon::parse('2026-11-15 09:00:00'));

    Event::factory()->create([
        'name' => 'Ceramics Workshop',
        'start_date_time' => '2026-11-20 18:00:00',
        'end_date_time' => '2026-11-20 20:00:00',
    ]);
    Event::factory()->create([
        'name' => 'Startup Pitch Night',
        'start_date_time' => '2026-12-05 18:00:00',
        'end_date_time' => '2026-12-05 20:00:00',
    ]);

    $this->get(route('events.index', ['date' => 'next_month']))
        ->assertOk()
        ->assertSee('Startup Pitch Night')
        ->assertDontSee('Ceramics Workshop');
});

it('combines a keyword with a category filter', function () {
    $music = Category::factory()->create(['name' => 'Music']);
    $tech = Category::factory()->create(['name' => 'Tech']);

    Event::factory()->for($music)->create(['name' => 'Ceramics Workshop']);
    Event::factory()->for($tech)->create(['name' => 'Ceramics Hackathon']);

    $this->get(route('events.index', ['q' => 'Ceramics', 'category' => $music->id]))
        ->assertOk()
        ->assertSee('Ceramics Workshop')
        ->assertDontSee('Ceramics Hackathon');
});

it('ignores a city that is not on our list', function () {
    Event::factory()->create(['name' => 'Ceramics Workshop']);

    $this->get(route('events.index', ['city' => 'Paris']))
        ->assertOk()
        ->assertSee('Ceramics Workshop')
        ->assertDontSee('No results found');
});

it('ignores an unrecognised date preset', function () {
    Event::factory()->create(['name' => 'Ceramics Workshop']);

    $this->get(route('events.index', ['date' => 'banana']))
        ->assertOk()
        ->assertSee('Ceramics Workshop')
        ->assertDontSee('No results found');
});

it('ignores a one-character keyword', function () {
    Event::factory()->create(['name' => 'Ceramics Workshop']);

    $this->get(route('events.index', ['q' => 'a']))
        ->assertOk()
        ->assertSee('Ceramics Workshop')
        ->assertDontSee('No results found');
});

it('keeps the filters when paging', function () {
    Event::factory()->count(13)->sequence(fn ($sequence) => [
        'name' => 'Summit Session '.($sequence->index + 1),
    ])->create();

    $this->get(route('events.index', ['q' => 'Summit']))
        ->assertOk()
        ->assertSee('q=Summit');
});

it('filters events to this week', function () {
    $this->travelTo(Carbon::parse('2026-11-18 09:00:00'));

    // Same day is always inside the Sunday-to-Saturday window; ten days out
    // is always outside it, whichever weekday the pinned date happens to be.
    Event::factory()->create([
        'name' => 'Ceramics Workshop',
        'start_date_time' => '2026-11-18 20:00:00',
        'end_date_time' => '2026-11-18 22:00:00',
    ]);
    Event::factory()->create([
        'name' => 'Startup Pitch Night',
        'start_date_time' => '2026-11-28 20:00:00',
        'end_date_time' => '2026-11-28 22:00:00',
    ]);

    $this->get(route('events.index', ['date' => 'this_week']))
        ->assertOk()
        ->assertSee('Ceramics Workshop')
        ->assertDontSee('Startup Pitch Night');
});

/*
|--------------------------------------------------------------------------
| Removed events (FR-5.4, UC7-E1)
|--------------------------------------------------------------------------
*/

it('shows an Event Unavailable page for an event the organizer removed', function () {
    $event = Event::factory()->create(['name' => 'Harbour Night Market']);
    $event->delete();

    $this->get(route('events.show', $event))
        ->assertNotFound()
        ->assertSee('Event Unavailable')
        ->assertDontSee('Harbour Night Market');
});

it('hides soft-deleted events from the public listing', function () {
    Event::factory()->create(['name' => 'Still Running Gig']);
    $removed = Event::factory()->create(['name' => 'Withdrawn Gig']);
    $removed->delete();

    $this->get(route('events.index'))
        ->assertOk()
        ->assertSee('Still Running Gig')
        ->assertDontSee('Withdrawn Gig');
});

it('keeps a removed event out of search results', function () {
    $removed = Event::factory()->create(['name' => 'Withdrawn Gig']);
    $removed->delete();

    $this->get(route('events.index', ['q' => 'Withdrawn']))
        ->assertOk()
        ->assertSee('No results found')
        ->assertDontSee('Withdrawn Gig');
});
