<?php

use App\Models\Event;
use App\Models\User;

it('serves the site in English by default', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Welcome back')
        ->assertSee('dir="ltr"', false);
});

it('switches to Arabic and remembers the choice', function () {
    $this->from(route('login'))
        ->get(route('locale.switch', 'ar'))
        ->assertRedirect(route('login'));

    expect(session('locale'))->toBe('ar');

    // The next request, with no locale in the URL, is still Arabic.
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('أهلًا بعودتك', false)
        ->assertDontSee('Welcome back');
});

it('renders the Arabic pages right to left', function () {
    $this->withSession(['locale' => 'ar'])
        ->get(route('login'))
        ->assertOk()
        ->assertSee('dir="rtl"', false);
});

it('renders the English pages left to right', function () {
    $this->withSession(['locale' => 'en'])
        ->get(route('login'))
        ->assertOk()
        ->assertSee('dir="ltr"', false);
});

it('applies the language to the admin area too', function () {
    $this->withSession(['locale' => 'ar'])
        ->get(route('admin.login'))
        ->assertOk()
        ->assertSee('dir="rtl"', false);
});

it('rejects a language we do not support', function () {
    $this->get('/locale/fr')->assertNotFound();

    expect(session('locale'))->toBeNull();
});

it('translates a validation message into Arabic', function () {
    $response = $this->withSession(['locale' => 'ar'])
        ->post(route('register'), [
            'name' => 'Nadia Barghathi',
            'email' => 'nadia@example.com',
            'phone_number' => '+15551234567',
            'dob' => '1994-07-02',
            'location' => 'Tripoli',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertSessionHasErrors('phone_number');

    // The custom message lives in lang/ar/validation.php rather than a
    // messages() override, which is what lets it translate at all.
    expect(session('errors')->first('phone_number'))->toContain('رقم هاتف ليبي');
});

it('keeps the language after signing in', function () {
    $user = User::factory()->create();

    $this->withSession(['locale' => 'ar'])
        ->post(route('login'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('home'));

    // Signing in regenerates the session; the choice has to survive that.
    $this->get(route('home'))->assertOk()->assertSee('dir="rtl"', false);
});

it('offers a reachable language switch in the menu', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('locale.switch', 'ar'), false)
        ->assertSee(route('locale.switch', 'en'), false);
});

it('renders the public pages in Arabic', function () {
    Event::factory()->create();

    $this->withSession(['locale' => 'ar'])
        ->get(route('events.index'))
        ->assertOk()
        ->assertSee('اعثر على فعاليتك القادمة', false)
        ->assertDontSee('Find your next event');
});

it('pluralises a count in Arabic', function () {
    Event::factory()->create(['city' => 'Benghazi']);

    // Arabic has more plural forms than English; trans_choice picks between
    // them, so a filtered listing must not fall back to the English string.
    $this->withSession(['locale' => 'ar'])
        ->get(route('events.index', ['city' => 'Benghazi']))
        ->assertOk()
        ->assertDontSee('event found')
        ->assertDontSee('events found');
});
