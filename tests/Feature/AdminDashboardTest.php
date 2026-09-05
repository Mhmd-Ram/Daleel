<?php

use App\Models\Admin;
use App\Models\Event;
use App\Models\OrganizerApplication;
use App\Models\User;

it('sends a guest to the admin login', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
});

it('shows site-wide counts to a signed-in admin', function () {
    $admin = Admin::factory()->create();
    $organizer = User::factory()->organizer()->create();

    Event::factory()->count(3)->recycle($admin)->create();
    Event::factory()->inactive()->recycle($admin)->create();
    Event::factory()->organizedBy($organizer)->create();

    $this->actingAs($admin, 'admin')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard')
        // 5 events in total, 4 of them published.
        ->assertSee('4 published, 1 draft');
});

it('links to the review queue when applications are waiting', function () {
    OrganizerApplication::factory()->count(2)->create();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('applications waiting for review', false)
        ->assertSee(route('admin.organizer-applications.index'), false);
});

it('hides the review prompt when the queue is empty', function () {
    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('waiting for review');
});

it('names the creator of each recent event, admin or organizer', function () {
    $admin = Admin::factory()->create(['name' => 'Site Admin']);
    $organizer = User::factory()->organizer()->create(['name' => 'Amal Zarrouk']);

    Event::factory()->recycle($admin)->create(['name' => 'Admin Run Event']);
    Event::factory()->organizedBy($organizer)->create(['name' => 'Organizer Run Event']);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Site Admin')
        ->assertSee('Amal Zarrouk');
});

it('lands an admin on the dashboard after signing in', function () {
    $admin = Admin::factory()->create(['email' => 'staff@daleel.ly']);

    $this->post(route('admin.login'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));
});
