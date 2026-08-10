<?php

use App\Models\Admin;
use App\Models\Category;
use App\Models\Event;
use App\Models\User;

function eventPayload(Category $category): array
{
    return [
        'name' => 'Benghazi Design Jam',
        'description' => 'A hands-on afternoon for designers and developers.',
        'location' => 'Waha Cultural Centre',
        'category_id' => $category->id,
        'start_date_time' => now()->addWeek()->format('Y-m-d\TH:i'),
        'end_date_time' => now()->addWeek()->addHours(4)->format('Y-m-d\TH:i'),
        'tiket_cost' => 0,
        'max_capacity' => 60,
        'is_active' => 1,
    ];
}

it('keeps the organizer area away from plain attendees', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('organizer.events.index'))
        ->assertForbidden();
});

it('keeps the organizer area away from guests', function () {
    $this->get(route('organizer.events.index'))->assertRedirect(route('login'));
});

it('renders the organizer create and edit forms', function () {
    $organizer = User::factory()->organizer()->create();
    Category::factory()->create(['name' => 'Community']);
    $event = Event::factory()->organizedBy($organizer)->create();

    $this->actingAs($organizer)
        ->get(route('organizer.events.create'))
        ->assertOk()
        ->assertSee('Community');

    $this->actingAs($organizer)
        ->get(route('organizer.events.edit', $event))
        ->assertOk()
        ->assertSee($event->name);
});

it('lets an organizer create an event they own', function () {
    $organizer = User::factory()->organizer()->create();
    $category = Category::factory()->create();

    $this->actingAs($organizer)
        ->post(route('organizer.events.store'), eventPayload($category))
        ->assertRedirect(route('organizer.events.index'));

    $event = Event::firstWhere('name', 'Benghazi Design Jam');

    expect($event)->not->toBeNull()
        ->and($event->organizer_id)->toBe($organizer->id)
        ->and($event->admin_id)->toBeNull()
        ->and($event->owner()->is($organizer))->toBeTrue();
});

it('lists only the events the organizer owns', function () {
    $organizer = User::factory()->organizer()->create();
    $other = User::factory()->organizer()->create();

    $mine = Event::factory()->organizedBy($organizer)->create(['name' => 'My Own Event']);
    $theirs = Event::factory()->organizedBy($other)->create(['name' => 'Someone Elses Event']);
    $adminOwned = Event::factory()->create(['name' => 'Admin Owned Event']);

    $this->actingAs($organizer)
        ->get(route('organizer.events.index'))
        ->assertOk()
        ->assertSee($mine->name)
        ->assertDontSee($theirs->name)
        ->assertDontSee($adminOwned->name);
});

it('stops an organizer editing another organizer\'s event', function () {
    $organizer = User::factory()->organizer()->create();
    $event = Event::factory()->organizedBy(User::factory()->organizer()->create())->create();

    $this->actingAs($organizer)->get(route('organizer.events.edit', $event))->assertForbidden();
    $this->actingAs($organizer)->delete(route('organizer.events.destroy', $event))->assertForbidden();

    expect(Event::whereKey($event->id)->exists())->toBeTrue();
});

it('stops an organizer touching an admin-owned event', function () {
    $organizer = User::factory()->organizer()->create();
    $event = Event::factory()->create();

    $this->actingAs($organizer)->get(route('organizer.events.edit', $event))->assertForbidden();
});

it('lets an organizer update and delete their own event', function () {
    $organizer = User::factory()->organizer()->create();
    $category = Category::factory()->create();
    $event = Event::factory()->organizedBy($organizer)->create();

    $this->actingAs($organizer)
        ->put(route('organizer.events.update', $event), [...eventPayload($category), 'name' => 'Renamed Event'])
        ->assertRedirect(route('organizer.events.index'));

    expect($event->refresh()->name)->toBe('Renamed Event')
        ->and($event->organizer_id)->toBe($organizer->id);

    $this->actingAs($organizer)->delete(route('organizer.events.destroy', $event));

    expect(Event::whereKey($event->id)->exists())->toBeFalse();
});

it('still lets an admin create events alongside organizers', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($admin, 'admin')
        ->post(route('admin.events.store'), eventPayload($category))
        ->assertRedirect(route('admin.events.index'));

    $event = Event::firstWhere('name', 'Benghazi Design Jam');

    expect($event->admin_id)->toBe($admin->id)
        ->and($event->organizer_id)->toBeNull();
});

it('refuses to save an event with no owner', function () {
    $event = Event::factory()->make(['admin_id' => null, 'organizer_id' => null]);

    expect(fn () => $event->save())->toThrow(LogicException::class);
});

it('refuses to save an event owned by both an admin and an organizer', function () {
    $organizer = User::factory()->organizer()->create();
    $event = Event::factory()->make(['organizer_id' => $organizer->id]);

    expect(fn () => $event->save())->toThrow(LogicException::class);
});

it('shows organizer-owned events on the public listing', function () {
    $organizer = User::factory()->organizer()->create();
    $event = Event::factory()->organizedBy($organizer)->create(['name' => 'Public Organizer Event']);

    $this->get(route('events.index'))->assertOk()->assertSee($event->name);
});
