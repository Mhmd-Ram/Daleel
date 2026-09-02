<?php

use App\Models\Admin;
use App\Models\Category;
use App\Models\Event;
use App\Models\User;

it('keeps the admin area behind the admin guard', function () {
    $this->get(route('admin.events.index'))->assertRedirect(route('admin.login'));
});

it('does not let a regular user reach the admin area', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.events.index'))
        ->assertRedirect(route('admin.login'));
});

it('lets an admin create an event with a chosen category', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.events.store'), [
        'name' => 'Launch Night',
        'description' => 'An evening of demos.',
        'location' => 'Tripoli',
        'city' => 'Tripoli',
        'category_id' => $category->id,
        'start_date_time' => now()->addWeek()->format('Y-m-d\TH:i'),
        'end_date_time' => now()->addWeek()->addHours(3)->format('Y-m-d\TH:i'),
        'tiket_cost' => 0,
        'is_active' => '1',
    ])->assertRedirect(route('admin.events.index'));

    expect(Event::where('name', 'Launch Night')->where('admin_id', $admin->id)->exists())->toBeTrue();
});

it('rejects an event whose end is before its start', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.events.store'), [
        'name' => 'Bad Times',
        'description' => 'Nope.',
        'location' => 'Tripoli',
        'city' => 'Tripoli',
        'category_id' => $category->id,
        'start_date_time' => now()->addWeek()->addHours(3)->format('Y-m-d\TH:i'),
        'end_date_time' => now()->addWeek()->format('Y-m-d\TH:i'),
        'tiket_cost' => 0,
    ])->assertSessionHasErrors('end_date_time');
});

it('rejects a negative ticket cost', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.events.store'), [
        'name' => 'Cheap',
        'description' => 'Nope.',
        'location' => 'Tripoli',
        'city' => 'Tripoli',
        'category_id' => $category->id,
        'start_date_time' => now()->addWeek()->format('Y-m-d\TH:i'),
        'end_date_time' => now()->addWeek()->addHours(2)->format('Y-m-d\TH:i'),
        'tiket_cost' => -5,
    ])->assertSessionHasErrors('tiket_cost');
});

it('toggles publish state via the is_active flag', function () {
    $admin = Admin::factory()->create();
    $event = Event::factory()->inactive()->for($admin)->create();

    $this->actingAs($admin, 'admin')->patch(route('admin.events.publish', $event));

    expect($event->fresh()->is_active)->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Moderation of every event (UC16, FR-12.2, FR-12.3)
|--------------------------------------------------------------------------
|
| This area used to be ownership-scoped, and a test here asserted that an
| admin could NOT touch another admin's event. That is the opposite of what
| the SRS asks for, so it was rewritten into the cases below rather than
| deleted.
*/

it('lets an admin edit an event another admin created', function () {
    $owner = Admin::factory()->create();
    $moderator = Admin::factory()->create();
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($moderator, 'admin')
        ->get(route('admin.events.edit', $event))
        ->assertOk();
});

it("lets an admin edit an organizer's event", function () {
    $organizer = User::factory()->organizer()->create();
    $event = Event::factory()->organizedBy($organizer)->create();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.events.edit', $event))
        ->assertOk();
});

it('keeps the organizer as the owner when an admin edits their event', function () {
    $organizer = User::factory()->organizer()->create();
    $category = Category::factory()->create();
    $event = Event::factory()->organizedBy($organizer)->create();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->put(route('admin.events.update', $event), [
            'name' => 'Moderated Title',
            'description' => 'Edited by an admin.',
            'location' => 'Tripoli',
            'city' => 'Tripoli',
            'category_id' => $category->id,
            'start_date_time' => now()->addWeek()->format('Y-m-d\TH:i'),
            'end_date_time' => now()->addWeek()->addHours(3)->format('Y-m-d\TH:i'),
            'tiket_cost' => 0,
            'is_active' => '1',
        ])->assertRedirect(route('admin.events.index'));

    // The Event::saving invariant allows exactly one owner. Neither owner
    // column is in the request rules, so moderating cannot transfer ownership.
    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'name' => 'Moderated Title',
        'organizer_id' => $organizer->id,
        'admin_id' => null,
    ]);
});

it("lets an admin delete an organizer's event", function () {
    $organizer = User::factory()->organizer()->create();
    $event = Event::factory()->organizedBy($organizer)->create();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->delete(route('admin.events.destroy', $event))
        ->assertRedirect(route('admin.events.index'));

    // Soft-deleted since Phase 8: the row survives so the event page can
    // explain itself, but it is gone from every listing.
    $this->assertSoftDeleted('events', ['id' => $event->id]);
});

it("lets an admin unpublish an organizer's event", function () {
    $organizer = User::factory()->organizer()->create();
    $event = Event::factory()->organizedBy($organizer)->create(['is_active' => true]);

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->from(route('admin.events.index'))
        ->patch(route('admin.events.publish', $event));

    expect($event->fresh()->is_active)->toBeFalse();
});

it('lists events from every owner', function () {
    $otherAdmin = Admin::factory()->create();
    $organizer = User::factory()->organizer()->create();

    Event::factory()->for($otherAdmin)->create(['name' => 'Ministry Briefing']);
    Event::factory()->organizedBy($organizer)->create(['name' => 'Community Potluck']);

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.events.index'))
        ->assertOk()
        ->assertSee('Ministry Briefing')
        ->assertSee('Community Potluck');
});

it('filters the event list by owner type', function () {
    $organizer = User::factory()->organizer()->create();
    Event::factory()->for(Admin::factory()->create())->create(['name' => 'Ministry Briefing']);
    Event::factory()->organizedBy($organizer)->create(['name' => 'Community Potluck']);

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.events.index', ['owner' => 'organizer']))
        ->assertOk()
        ->assertSee('Community Potluck')
        ->assertDontSee('Ministry Briefing');
});

it('filters the event list by published status', function () {
    $admin = Admin::factory()->create();
    Event::factory()->for($admin)->create(['name' => 'Ministry Briefing']);
    Event::factory()->for($admin)->inactive()->create(['name' => 'Community Potluck']);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.events.index', ['status' => 'draft']))
        ->assertOk()
        ->assertSee('Community Potluck')
        ->assertDontSee('Ministry Briefing');
});

it('ignores an unrecognised owner or status filter', function () {
    $admin = Admin::factory()->create();
    Event::factory()->for($admin)->create(['name' => 'Ministry Briefing']);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.events.index', ['owner' => 'wizard', 'status' => 'melted']))
        ->assertOk()
        ->assertSee('Ministry Briefing');
});

it('shows who created each event', function () {
    $organizer = User::factory()->organizer()->create(['name' => 'Huda Zarrouk']);
    Event::factory()->organizedBy($organizer)->create(['name' => 'Community Potluck']);

    // The filter dropdown also contains the word "Organizer", so this asserts
    // the pill that follows the event rather than the option.
    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.events.index'))
        ->assertOk()
        ->assertSee('Huda Zarrouk')
        ->assertSeeInOrder(['Community Potluck', 'Huda Zarrouk', 'Organizer']);
});

it('shows the list of users registered for an event', function () {
    $admin = Admin::factory()->create();
    $event = Event::factory()->for($admin)->create();
    $user = User::factory()->create(['name' => 'Registered Person']);
    $user->registrations()->attach($event, ['created_at' => now()]);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.events.registrations', $event))
        ->assertOk()
        ->assertSee('Registered Person');
});
