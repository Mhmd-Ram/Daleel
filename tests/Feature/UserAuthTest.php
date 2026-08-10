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
