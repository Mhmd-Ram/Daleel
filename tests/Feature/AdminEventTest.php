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

it('does not let an admin edit an event they do not own', function () {
    $owner = Admin::factory()->create();
    $intruder = Admin::factory()->create();
    $event = Event::factory()->for($owner)->create();

    $this->actingAs($intruder, 'admin')
        ->get(route('admin.events.edit', $event))
        ->assertForbidden();
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
