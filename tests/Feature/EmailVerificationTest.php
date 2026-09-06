<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Mockery\MockInterface;
use Symfony\Component\Mailer\Exception\TransportException;

/**
 * Make every notification send blow up the way a refused SMTP connection does,
 * so the controllers' transport-failure handling can be exercised.
 */
function failTheMailTransport(): void
{
    test()->mock(Dispatcher::class, function (MockInterface $mock) {
        $mock->shouldReceive('send')->andThrow(new TransportException('Connection refused'));
        $mock->shouldIgnoreMissing();
    });
}

it('blocks an unverified user from registering for an event', function () {
    $user = User::factory()->unverified()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->post(route('events.register', $event))
        ->assertRedirect(route('verification.notice'));

    expect($event->registeredUsers()->count())->toBe(0);
});

it('blocks an unverified user from applying to become an organizer', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->post(route('organizer.apply'), ['message' => str_repeat('I want to run meetups. ', 3)])
        ->assertRedirect(route('verification.notice'));

    expect($user->organizerApplications()->count())->toBe(0);
});

it('shows the verification notice to an unverified user', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('verification.notice'))
        ->assertOk()
        ->assertSee('Verify your email');
});

it('sends a verified user away from the notice page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('verification.notice'))
        ->assertRedirect(route('home'));
});

it('marks the account verified when the signed link is opened', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->actingAs($user)->get($url)->assertRedirect(route('home'));

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
});

it('does not verify the account when the link signature is wrong', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1('someone-elses@example.com'),
    ]);

    $this->actingAs($user)->get($url)->assertForbidden();

    expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
});

it('lets a verified user register for an event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)->post(route('events.register', $event));

    expect($event->registeredUsers()->whereKey($user->id)->exists())->toBeTrue();
});

it('says the resend worked when it did', function () {
    $this->actingAs(User::factory()->unverified()->create())
        ->post(route('verification.send'))
        ->assertRedirect()
        ->assertSessionHas('success')
        ->assertSessionMissing('error');
});

it('admits it when the resend cannot reach the mail server', function () {
    failTheMailTransport();

    $this->actingAs(User::factory()->unverified()->create())
        ->post(route('verification.send'))
        ->assertRedirect()
        ->assertSessionHas('error')
        ->assertSessionMissing('success');
});

it('still creates the account and signs the user in when the welcome email fails', function () {
    failTheMailTransport();

    $this->post(route('register'), [
        'name' => 'Nour Belkacem',
        'email' => 'nour@example.com',
        'phone_number' => '+218914445555',
        'dob' => '1994-11-02',
        'location' => 'Misrata',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertRedirect(route('verification.notice'))
        ->assertSessionHas('error');

    // The account is real and usable even though the email never left.
    $this->assertAuthenticated();
    expect(User::where('email', 'nour@example.com')->exists())->toBeTrue();
});

it('sends the verification email immediately rather than queuing it', function () {
    Notification::fake();

    $this->post(route('register'), [
        'name' => 'Nadia Fathi',
        'email' => 'nadia@example.com',
        'phone_number' => '+218913334455',
        'dob' => '1996-04-11',
        'location' => 'Tripoli',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::firstWhere('email', 'nadia@example.com');

    Notification::assertSentTo($user, VerifyEmail::class);

    // Deliberately not queued. A queued verification depends on a worker being
    // alive, and when one is not, every new account is stranded with no way to
    // activate it and nothing on screen to say so. Registration already
    // tolerates a refused SMTP connection, so inline is the safer failure.
    expect(new VerifyEmail)->not->toBeInstanceOf(ShouldQueue::class);
});
