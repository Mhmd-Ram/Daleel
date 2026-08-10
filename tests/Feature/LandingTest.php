<?php

use App\Models\Event;

it('shows the landing page with a browse call to action', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Browse events')
        ->assertSee(route('events.index'))
        ->assertSee(route('admin.login'));
});

it('features upcoming events on the landing page', function () {
    Event::factory()->create(['name' => 'Headline Festival']);

    $this->get(route('home'))->assertOk()->assertSee('Headline Festival');
});

it('does not feature inactive events on the landing page', function () {
    Event::factory()->inactive()->create(['name' => 'Secret Draft']);

    $this->get(route('home'))->assertOk()->assertDontSee('Secret Draft');
});
