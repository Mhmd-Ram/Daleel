<?php

use App\Mail\EventRegistered;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

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

it('renders the organizer area in Arabic', function () {
    $organizer = User::factory()->organizer()->create();

    $this->actingAs($organizer)
        ->withSession(['locale' => 'ar'])
        ->get(route('organizer.events.index'))
        ->assertOk()
        ->assertSee('الفعاليات التي تنظّمها', false)
        ->assertDontSee('Events you organize');
});

it('keeps the delete confirmation usable in Arabic', function () {
    $organizer = User::factory()->organizer()->create();
    Event::factory()->organizedBy($organizer)->create();

    // The confirm() text sits inside a single-quoted JS string inside a
    // double-quoted attribute, so an apostrophe in either language would
    // break the markup. Neither has one; this pins that.
    $this->actingAs($organizer)
        ->withSession(['locale' => 'ar'])
        ->get(route('organizer.events.index'))
        ->assertOk()
        ->assertDontSee('&#039;', false);
});

it('renders the admin area in Arabic', function () {
    $this->actingAs(Admin::factory()->create(), 'admin')
        ->withSession(['locale' => 'ar'])
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('إدارة المستخدمين', false)
        ->assertDontSee('User management');
});

it('sends the confirmation email in the language the user was browsing', function () {
    Mail::fake();

    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->withSession(['locale' => 'ar'])
        ->from(route('events.show', $event))
        ->post(route('events.register', $event));

    // The queue has no session, so the locale must be pinned at dispatch or
    // the mail silently renders in English.
    Mail::assertQueued(
        EventRegistered::class,
        fn ($mail) => $mail->locale === 'ar',
    );
});

it('translates a controller flash message', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->withSession(['locale' => 'ar'])
        ->from(route('events.show', $event))
        ->post(route('events.register', $event))
        ->assertSessionHas('success', fn ($message) => str_contains($message, 'محفوظة في تقويمك'));
});

it('translates the pagination controls', function () {
    Event::factory()->count(13)->create();

    $this->withSession(['locale' => 'ar'])
        ->get(route('events.index'))
        ->assertOk()
        ->assertSee('التالي', false)
        ->assertDontSee('Next');
});

it('translates the login failure message', function () {
    $this->withSession(['locale' => 'ar'])
        ->post(route('login'), ['email' => 'nobody@example.com', 'password' => 'wrong-password'])
        ->assertInvalid(['email' => 'بيانات الدخول']);
});

it('translates the ban message on the login screen', function () {
    $user = User::factory()->banned()->create();

    $this->withSession(['locale' => 'ar'])
        ->post(route('login'), ['email' => $user->email, 'password' => 'password'])
        ->assertInvalid(['email' => 'تم حظر هذا الحساب']);
});

it('translates a validation message for the coordinate pair', function () {
    $organizer = User::factory()->organizer()->create();
    $category = Category::factory()->create();

    // required_with fires when only half a map pin is submitted.
    $this->actingAs($organizer)
        ->withSession(['locale' => 'ar'])
        ->post(route('organizer.events.store'), [
            'name' => 'Test', 'description' => 'Test', 'location' => 'Tripoli', 'city' => 'Tripoli',
            'category_id' => $category->id,
            'start_date_time' => now()->addWeek()->format('Y-m-d\TH:i'),
            'end_date_time' => now()->addWeek()->addHours(2)->format('Y-m-d\TH:i'),
            'tiket_cost' => 0, 'latitude' => 32.8,
        ])
        ->assertInvalid(['longitude' => 'مطلوب']);
});
