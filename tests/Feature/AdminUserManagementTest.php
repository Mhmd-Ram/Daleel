<?php

use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\Event;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Access (UC12)
|--------------------------------------------------------------------------
*/

it('keeps the user list behind the admin guard', function () {
    $this->get(route('admin.users.index'))->assertRedirect(route('admin.login'));
});

it('does not let a regular user reach the user list', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.index'))
        ->assertRedirect(route('admin.login'));
});

it('never lists administrator accounts', function () {
    $admin = Admin::factory()->create(['email' => 'sysadmin@daleel.test']);
    User::factory()->create(['name' => 'Nadia Barghathi']);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Nadia Barghathi')
        ->assertDontSee('sysadmin@daleel.test');
});

/*
|--------------------------------------------------------------------------
| Listing and filters (FR-10.1, FR-10.2)
|--------------------------------------------------------------------------
*/

it('lists registered users with their role and status', function () {
    $admin = Admin::factory()->create();
    User::factory()->organizer()->create(['name' => 'Yusra Fathallah', 'email' => 'yusra@daleel.test']);
    User::factory()->banned()->create(['name' => 'Tariq Belkhir', 'email' => 'tariq@daleel.test']);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.users.index'))->assertOk();

    // The filter dropdown also contains the words "Organizer" and "Banned", so
    // these assert the badge that follows each name rather than the option.
    $response->assertSee('yusra@daleel.test')
        ->assertSee('tariq@daleel.test')
        ->assertSeeInOrder(['Yusra Fathallah', 'Organizer'])
        ->assertSeeInOrder(['Tariq Belkhir', 'Banned']);
});

it('filters the user list by role', function () {
    $admin = Admin::factory()->create();
    User::factory()->organizer()->create(['name' => 'Yusra Fathallah']);
    User::factory()->create(['name' => 'Tariq Belkhir']);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.users.index', ['role' => 'organizer']))
        ->assertOk()
        ->assertSee('Yusra Fathallah')
        ->assertDontSee('Tariq Belkhir');
});

it('filters the user list by status', function () {
    $admin = Admin::factory()->create();
    User::factory()->banned()->create(['name' => 'Tariq Belkhir']);
    User::factory()->create(['name' => 'Yusra Fathallah']);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.users.index', ['status' => 'banned']))
        ->assertOk()
        ->assertSee('Tariq Belkhir')
        ->assertDontSee('Yusra Fathallah');
});

it('finds a user by name or email', function () {
    $admin = Admin::factory()->create();
    User::factory()->create(['name' => 'Yusra Fathallah', 'email' => 'yusra@daleel.test']);
    User::factory()->create(['name' => 'Tariq Belkhir', 'email' => 'tariq@daleel.test']);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.users.index', ['q' => 'Yusra']))
        ->assertOk()
        ->assertSee('Yusra Fathallah')
        ->assertDontSee('Tariq Belkhir');

    $this->actingAs($admin, 'admin')
        ->get(route('admin.users.index', ['q' => 'tariq@daleel']))
        ->assertOk()
        ->assertSee('Tariq Belkhir')
        ->assertDontSee('Yusra Fathallah');
});

it('ignores a role that is not on our list', function () {
    $admin = Admin::factory()->create();
    User::factory()->create(['name' => 'Yusra Fathallah']);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.users.index', ['role' => 'wizard', 'status' => 'melted']))
        ->assertOk()
        ->assertSee('Yusra Fathallah');
});

/*
|--------------------------------------------------------------------------
| Ban and unban (UC13, FR-10.3, FR-10.4)
|--------------------------------------------------------------------------
*/

it('bans a user', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($admin, 'admin')
        ->from(route('admin.users.index'))
        ->patch(route('admin.users.ban', $user))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    expect($user->fresh()->isBanned())->toBeTrue();
});

it('refuses to ban a user who is already banned', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->banned()->create();

    $this->actingAs($admin, 'admin')
        ->patch(route('admin.users.ban', $user))
        ->assertStatus(409);
});

it('lifts a ban', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->banned()->create();

    $this->actingAs($admin, 'admin')
        ->from(route('admin.users.index'))
        ->patch(route('admin.users.unban', $user))
        ->assertRedirect(route('admin.users.index'));

    expect($user->fresh()->isBanned())->toBeFalse();
});

it('refuses to lift a ban on a user who is not banned', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($admin, 'admin')
        ->patch(route('admin.users.unban', $user))
        ->assertStatus(409);
});

/*
|--------------------------------------------------------------------------
| Revert role (UC14, FR-10.5, FR-10.6)
|--------------------------------------------------------------------------
*/

it('reverts an organizer to an attendee', function () {
    $admin = Admin::factory()->create();
    $organizer = User::factory()->organizer()->create();

    $this->actingAs($admin, 'admin')
        ->from(route('admin.users.index'))
        ->patch(route('admin.users.revert-role', $organizer))
        ->assertRedirect(route('admin.users.index'));

    expect($organizer->fresh()->role)->toBe(UserRole::Attendee);
});

it('refuses to revert someone who is not an organizer', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($admin, 'admin')
        ->patch(route('admin.users.revert-role', $user))
        ->assertStatus(409);
});

it("leaves a reverted organizer's events published", function () {
    $admin = Admin::factory()->create();
    $organizer = User::factory()->organizer()->create();
    $event = Event::factory()->organizedBy($organizer)->create();

    $this->actingAs($admin, 'admin')
        ->from(route('admin.users.index'))
        ->patch(route('admin.users.revert-role', $organizer));

    // Demoting the organizer must not punish the attendees who already saved
    // their events, so the events keep their owner and stay published.
    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'organizer_id' => $organizer->id,
        'is_active' => true,
    ]);
});

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

it('counts banned users on the dashboard', function () {
    $admin = Admin::factory()->create();
    User::factory()->count(2)->banned()->create();
    User::factory()->create();

    $this->actingAs($admin, 'admin')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('2 banned');
});

it('blocks the login of a user the admin just banned', function () {
    $admin = Admin::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($admin, 'admin')
        ->from(route('admin.users.index'))
        ->patch(route('admin.users.ban', $user));

    // actingAs on the admin guard makes it the default driver for the rest of
    // the test, which would make the guest-only login route redirect. Reset to
    // the web guard so this exercises a real visitor reaching /login.
    $this->app['auth']->forgetGuards();
    $this->app['auth']->shouldUse('web');

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertInvalid(['email' => 'banned']);

    $this->assertGuest();
});
