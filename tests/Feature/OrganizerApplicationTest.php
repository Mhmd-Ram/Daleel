<?php

use App\Enums\OrganizerApplicationStatus;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\OrganizerApplication;
use App\Models\User;
use Illuminate\Database\QueryException;

$pitch = 'I run a monthly meetup for web developers and would like to list it here.';

it('lets a verified user apply to become an organizer', function () use ($pitch) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('organizer.apply'), ['message' => $pitch])
        ->assertRedirect(route('profile.show'));

    expect($user->organizerApplications()->count())->toBe(1)
        ->and($user->pendingOrganizerApplication())->not->toBeNull();
});

it('shows the right organizer prompt on the profile page for each kind of user', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('profile.show'))
        ->assertOk()
        ->assertSee('Become an organizer');

    $this->actingAs(User::factory()->unverified()->create())
        ->get(route('profile.show'))
        ->assertOk()
        ->assertSee('Verify your email first', false);

    $this->actingAs(User::factory()->organizer()->create())
        ->get(route('profile.show'))
        ->assertOk()
        ->assertSee('You are an organizer');

    $waiting = User::factory()->create();
    OrganizerApplication::factory()->for($waiting)->create();
    $this->actingAs($waiting)
        ->get(route('profile.show'))
        ->assertOk()
        ->assertSee('Application under review');
});

it('rejects an application that is too short to review', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('organizer.apply'), ['message' => 'let me in'])
        ->assertSessionHasErrors('message');

    expect($user->organizerApplications()->count())->toBe(0);
});

it('refuses a second application while one is still pending', function () use ($pitch) {
    $user = User::factory()->create();
    OrganizerApplication::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('organizer.apply'), ['message' => $pitch])
        ->assertForbidden();

    expect($user->organizerApplications()->count())->toBe(1);
});

it('refuses an application from someone who is already an organizer', function () use ($pitch) {
    $user = User::factory()->organizer()->create();

    $this->actingAs($user)
        ->post(route('organizer.apply'), ['message' => $pitch])
        ->assertForbidden();
});

it('lets the database reject a second pending application, whatever the app layer does', function () {
    $user = User::factory()->create();
    OrganizerApplication::factory()->for($user)->create();

    expect(fn () => OrganizerApplication::factory()->for($user)->create())
        ->toThrow(QueryException::class);
});

it('allows a fresh application once the previous one is decided', function () {
    $user = User::factory()->create();
    $first = OrganizerApplication::factory()->for($user)->create();

    $first->reject(Admin::factory()->create());

    OrganizerApplication::factory()->for($user)->create();

    expect($user->organizerApplications()->count())->toBe(2);
});

it('promotes the applicant when an admin approves', function () {
    $application = OrganizerApplication::factory()->create();
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->patch(route('admin.organizer-applications.approve', $application))
        ->assertRedirect();

    $application->refresh();

    expect($application->status)->toBe(OrganizerApplicationStatus::Approved)
        ->and($application->reviewed_by)->toBe($admin->id)
        ->and($application->reviewed_at)->not->toBeNull()
        ->and($application->user->refresh()->role)->toBe(UserRole::Organizer);
});

it('keeps the applicant an attendee when an admin rejects, and lets them reapply', function () use ($pitch) {
    $application = OrganizerApplication::factory()->create();
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')
        ->patch(route('admin.organizer-applications.reject', $application));

    $application->refresh();
    $user = $application->user;

    expect($application->status)->toBe(OrganizerApplicationStatus::Rejected)
        ->and($user->refresh()->role)->toBe(UserRole::Attendee);

    // A rejection is not a ban: the user can send a fresh application.
    $this->actingAs($user)
        ->post(route('organizer.apply'), ['message' => $pitch])
        ->assertRedirect(route('profile.show'));

    expect($user->organizerApplications()->count())->toBe(2);
});

it('refuses to review the same application twice', function () {
    $application = OrganizerApplication::factory()->create();
    $admin = Admin::factory()->create();

    $this->actingAs($admin, 'admin')->patch(route('admin.organizer-applications.approve', $application));

    $this->actingAs($admin, 'admin')
        ->patch(route('admin.organizer-applications.reject', $application))
        ->assertStatus(409);

    expect($application->refresh()->status)->toBe(OrganizerApplicationStatus::Approved);
});

it('keeps the review queue behind the admin guard', function () {
    $this->get(route('admin.organizer-applications.index'))->assertRedirect(route('admin.login'));

    $this->actingAs(User::factory()->create())
        ->get(route('admin.organizer-applications.index'))
        ->assertRedirect(route('admin.login'));
});

it('shows pending applications in the admin queue', function () {
    $application = OrganizerApplication::factory()->create();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->get(route('admin.organizer-applications.index'))
        ->assertOk()
        ->assertSee($application->user->name)
        ->assertSee($application->message);
});
