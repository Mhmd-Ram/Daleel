<?php

use App\Models\Admin;
use App\Models\Event;
use App\Models\User;

/**
 * Put an admin into site mode, the way the "View site" button does.
 *
 * `actingAs($admin, 'admin')` also makes `admin` the *default* guard, which no
 * real request ever does - Laravel resolves `web` from config, and that is what
 * `$request->user()` returns on a public route. Restoring it keeps the rest of
 * each test honest about what a visitor's request actually looks like.
 */
function enterSiteMode(Admin $admin): void
{
    test()->actingAs($admin, 'admin')->post(route('admin.view-site'));
    app('auth')->shouldUse('web');
}

it('signs an admin into the web guard when they open the site', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->post(route('admin.view-site'))
        ->assertRedirect(route('home'));

    $this->assertAuthenticated('web');
    $this->assertAuthenticated('admin');
});

it('creates the paired account once and reuses it', function () {
    $admin = Admin::factory()->create(['name' => 'Site Admin']);

    $first = $admin->ensureSiteUser();
    $second = $admin->fresh()->ensureSiteUser();

    expect($second->id)->toBe($first->id)
        ->and(User::where('staff_admin_id', $admin->id)->count())->toBe(1)
        ->and($first->name)->toBe('Site Admin')
        ->and($first->email_verified_at)->not->toBeNull();
});

it('gives the paired account the admin own address so mail reaches them', function () {
    $admin = Admin::factory()->create(['email' => 'staff@daleel.ly']);

    expect($admin->ensureSiteUser()->email)->toBe('staff@daleel.ly');
});

it('falls back to an unroutable address when a real account holds the email', function () {
    $admin = Admin::factory()->create(['email' => 'taken@example.com']);
    User::factory()->create(['email' => 'taken@example.com']);

    // Reserved TLD, so the fallback cannot reach anyone by accident.
    expect($admin->ensureSiteUser()->email)->toBe("admin-{$admin->id}@staff.invalid");
});

it('does not adopt a real account that shares the admin email', function () {
    $admin = Admin::factory()->create(['email' => 'shared@example.com']);
    $impostor = User::factory()->create(['email' => 'shared@example.com']);

    $paired = $admin->ensureSiteUser();

    // Adopting it would hand the admin someone else's account and history.
    expect($paired->id)->not->toBe($impostor->id)
        ->and($impostor->fresh()->staff_admin_id)->toBeNull();
});

it('lets an admin in site mode register for an event like anyone else', function () {
    $admin = Admin::factory()->create();
    $event = Event::factory()->create(['is_active' => true, 'max_capacity' => 10]);

    enterSiteMode($admin);

    $this->post(route('events.register', $event))->assertRedirect();

    // The point of the paired row: this foreign key needs a real users.id.
    expect($event->registeredUsers()->count())->toBe(1);
});

it('hides the paired account from the admin user list', function () {
    $admin = Admin::factory()->create(['name' => 'Hidden Staff']);
    $admin->ensureSiteUser();
    User::factory()->create(['name' => 'Real Person']);

    $this->actingAs($admin, 'admin')
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Real Person')
        ->assertDontSee('Hidden Staff');
});

it('leaves the paired account out of the dashboard user count', function () {
    $admin = Admin::factory()->create();
    User::factory()->count(3)->create();

    $before = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));
    $before->assertOk();

    $admin->ensureSiteUser();

    $this->actingAs($admin, 'admin')
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeText('3');
});

it('ends only the visitor session when leaving site mode', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.view-site'));
    $this->assertAuthenticated('web');

    $this->post(route('admin.exit-site'))->assertRedirect(route('admin.dashboard'));

    $this->assertGuest('web');
    $this->assertAuthenticated('admin');
});

it('keeps the admin signed in when they use the site log out', function () {
    $admin = Admin::factory()->create();

    enterSiteMode($admin);

    $this->post(route('logout'))->assertRedirect(route('admin.dashboard'));

    $this->assertGuest('web');
    $this->assertAuthenticated('admin');
});

it('ends both sessions when the admin logs out', function () {
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.view-site'));

    $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));

    $this->assertGuest('web');
    $this->assertGuest('admin');
});

it('still logs an ordinary user out completely', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest('web');
});

it('refuses to delete a paired account through the profile page', function () {
    $admin = Admin::factory()->create();
    $user = $admin->ensureSiteUser();

    enterSiteMode($admin);

    $this->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertForbidden();

    expect(User::find($user->id))->not->toBeNull();
});

it('cannot be signed into through the normal login form', function () {
    $admin = Admin::factory()->create();
    $user = $admin->ensureSiteUser();

    // The password is 64 random characters nobody holds, and there is no reset
    // flow, so this account is unreachable from the public login screen.
    $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest('web');
});

it('shows the way back to the dashboard while in site mode', function () {
    $admin = Admin::factory()->create();

    enterSiteMode($admin);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('admin.dashboard'))
        ->assertSee('browsing the site as an admin', escape: false);
});

it('shows no admin bar to an ordinary visitor', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee('browsing the site as an admin', escape: false);
});
