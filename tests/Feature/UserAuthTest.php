<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

it('registers a user with all required fields and logs them in', function () {
    $response = $this->post(route('register'), [
        'name' => 'Amina Said',
        'email' => 'amina@example.com',
        'phone_number' => '+218911234567',
        'dob' => '1996-03-14',
        'location' => 'Benghazi',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    // New accounts land on the verification notice, not the home page.
    $response->assertRedirect(route('verification.notice'));
    $this->assertAuthenticated();
    expect(User::where('email', 'amina@example.com')->exists())->toBeTrue();
});

it('fires the Registered event so a verification email goes out', function () {
    Event::fake();

    $this->post(route('register'), [
        'name' => 'Nabil Fathi',
        'email' => 'nabil@example.com',
        'phone_number' => '+218913333333',
        'dob' => '1994-07-02',
        'location' => 'Tripoli',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    Event::assertDispatched(Registered::class);
});

it('rejects a duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->post(route('register'), [
        'name' => 'Test',
        'email' => 'taken@example.com',
        'phone_number' => '+218910000000',
        'dob' => '1996-03-14',
        'location' => 'Tripoli',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('email');
});

it('rejects a duplicate phone number', function () {
    User::factory()->create(['phone_number' => '+218915555555']);

    $this->post(route('register'), [
        'name' => 'Test',
        'email' => 'new@example.com',
        'phone_number' => '+218915555555',
        'dob' => '1996-03-14',
        'location' => 'Tripoli',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('phone_number');
});

it('stores the password hashed, never in plain text', function () {
    $this->post(route('register'), [
        'name' => 'Hashed User',
        'email' => 'hash@example.com',
        'phone_number' => '+218914444444',
        'dob' => '1996-03-14',
        'location' => 'Tripoli',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'hash@example.com')->first();
    expect($user->password)->not->toBe('password123');
});

it('logs an existing user in with valid credentials', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

/*
|--------------------------------------------------------------------------
| Libyan phone number format (FR-2.2, UC1 step 3 / UC1-E2)
|--------------------------------------------------------------------------
*/

/**
 * A valid registration payload with the phone number swapped in.
 *
 * @return array<string, string>
 */
function registrationWithPhone(string $phone): array
{
    return [
        'name' => 'Nadia Barghathi',
        'email' => 'nadia'.substr(md5($phone), 0, 6).'@example.com',
        'phone_number' => $phone,
        'dob' => '1994-07-02',
        'location' => 'Tripoli',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ];
}

it('accepts the +218 international form', function () {
    $this->post(route('register'), registrationWithPhone('+218921234567'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('users', ['phone_number' => '+218921234567']);
});

it('accepts the 0-prefixed local form', function () {
    $this->post(route('register'), registrationWithPhone('0921234567'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('users', ['phone_number' => '0921234567']);
});

it('rejects a phone number that is not Libyan', function (string $phone) {
    $this->post(route('register'), registrationWithPhone($phone))
        ->assertSessionHasErrors('phone_number');

    $this->assertDatabaseMissing('users', ['phone_number' => $phone]);
})->with([
    'international non-Libyan' => '+15551234567',
    'wrong operator digit' => '+218961234567',
    'too short' => '+21891234567',
    'too long' => '+2189112345678',
    'no country or trunk prefix' => '911234567',
    'letters' => '+21891abc4567',
]);

it('explains the expected phone format', function () {
    $this->post(route('register'), registrationWithPhone('+15551234567'))
        ->assertInvalid(['phone_number' => 'Libyan mobile number']);
});

it('applies the same phone rule when editing a profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'phone_number' => '+15551234567',
        'dob' => '1994-07-02',
        'location' => 'Tripoli',
    ])->assertSessionHasErrors('phone_number');
});
