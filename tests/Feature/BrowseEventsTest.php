<?php

use App\Models\Category;
use App\Models\Event;

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

    $this->get(route('events.show', $event))->assertNotFound();
});
