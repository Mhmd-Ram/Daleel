<?php

use App\Models\Event;
use App\Models\OrganizerApplication;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Change password (FR-3.2, FR-3.3)
|--------------------------------------------------------------------------
*/

it('lets a user change their password with the correct current password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
        ->assertRedirect(route('profile.show'))
        ->assertSessionHas('success');

    expect(Hash::check('new-secret-password', $user->fresh()->password))->toBeTrue();
});

it('rejects a password change when the current password is wrong', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('profile.password'), [
            'current_password' => 'not-my-password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

it('rejects a password change when the confirmation does not match', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'a-different-password',
        ])
        ->assertSessionHasErrors('password');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

it('rejects a weak new password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
        ->assertSessionHasErrors('password');
});

it('stores the new password hashed, never in plain text', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.password'), [
        'current_password' => 'password',
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ]);

    $stored = $user->fresh()->password;

    expect($stored)->not->toBe('new-secret-password')
        ->and(Hash::check('new-secret-password', $stored))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Delete account (UC4, FR-3.4 - FR-3.6)
|--------------------------------------------------------------------------
*/

it('lets a user delete their own account', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect(route('home'));

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
    $this->assertGuest();
});

it('refuses to delete the account without the correct password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('profile.destroy'), ['password' => 'not-my-password'])
        ->assertSessionHasErrors('password');

    $this->assertDatabaseHas('users', ['id' => $user->id]);
    $this->assertAuthenticated();
});

it('deletes the user\'s calendar entries with the account', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();
    $user->registrations()->attach($event, ['created_at' => now()]);

    $this->assertDatabaseHas('user_regestrations', ['user_id' => $user->id, 'event_id' => $event->id]);

    $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password']);

    $this->assertDatabaseMissing('user_regestrations', ['user_id' => $user->id]);
    // The event itself belongs to an admin, so it outlives the attendee.
    $this->assertDatabaseHas('events', ['id' => $event->id]);
});

it('deletes an organizer application with the account', function () {
    $user = User::factory()->create();
    OrganizerApplication::factory()->for($user)->create();

    $this->actingAs($user)->delete(route('profile.destroy'), ['password' => 'password']);

    $this->assertDatabaseMissing('organizer_applications', ['user_id' => $user->id]);
});

it('deletes an organizer\'s events with their account', function () {
    $organizer = User::factory()->organizer()->create();
    $event = Event::factory()->organizedBy($organizer)->create();

    $attendee = User::factory()->create();
    $attendee->registrations()->attach($event, ['created_at' => now()]);

    $this->actingAs($organizer)->delete(route('profile.destroy'), ['password' => 'password']);

    $this->assertDatabaseMissing('events', ['id' => $event->id]);
    // The second-order cascade the danger zone warns about: another person's
    // saved entry goes with the event, not with their own account.
    $this->assertDatabaseMissing('user_regestrations', ['event_id' => $event->id]);
    $this->assertDatabaseHas('users', ['id' => $attendee->id]);
});

it('shows the organizer how many events deletion would destroy', function () {
    $organizer = User::factory()->organizer()->create();
    Event::factory()->count(3)->organizedBy($organizer)->create();

    $this->actingAs($organizer)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('You organize 3 events');
});

it('does not show the organizer warning to a plain attendee', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertDontSee('You organize');
});

it('keeps the profile routes behind auth', function () {
    $this->get(route('profile.edit'))->assertRedirect(route('login'));
    $this->put(route('profile.password'))->assertRedirect(route('login'));
    $this->delete(route('profile.destroy'))->assertRedirect(route('login'));
});

it('phrases the organizer warning in the singular for one event', function () {
    $organizer = User::factory()->organizer()->create();
    Event::factory()->organizedBy($organizer)->create();

    $this->actingAs($organizer)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('You organize 1 event.')
        ->assertSee('deletes it too');
});
