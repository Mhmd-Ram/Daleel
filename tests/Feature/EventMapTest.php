<?php

use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Http;

/**
 * A valid organizer event payload. Named differently from the eventPayload()
 * helper in OrganizerEventTest so the two cannot collide at load time.
 */
function mapEventPayload(Category $category, array $overrides = []): array
{
    return array_merge([
        'name' => 'Tripoli Street Food Night',
        'description' => 'An evening of food stalls along the corniche.',
        'location' => 'Martyrs Square, Tripoli',
        'city' => 'Tripoli',
        'category_id' => $category->id,
        'start_date_time' => now()->addWeek()->format('Y-m-d\TH:i'),
        'end_date_time' => now()->addWeek()->addHours(4)->format('Y-m-d\TH:i'),
        'tiket_cost' => 0,
        'max_capacity' => 200,
        'is_active' => 1,
    ], $overrides);
}

function nominatimReturns(array $places): void
{
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response($places),
    ]);
}

/*
|--------------------------------------------------------------------------
| Storing coordinates
|--------------------------------------------------------------------------
*/

it('stores the pin when an organizer places one', function () {
    $organizer = User::factory()->organizer()->create();
    $category = Category::factory()->create();

    $this->actingAs($organizer)->post(route('organizer.events.store'), mapEventPayload($category, [
        'latitude' => '32.8872000',
        'longitude' => '13.1913000',
    ]))->assertRedirect();

    $event = Event::firstWhere('name', 'Tripoli Street Food Night');

    expect($event->latitude)->toBe(32.8872)
        ->and($event->longitude)->toBe(13.1913)
        ->and($event->hasCoordinates())->toBeTrue();
});

it('saves an event with no pin at all', function () {
    $organizer = User::factory()->organizer()->create();
    $category = Category::factory()->create();

    $this->actingAs($organizer)->post(route('organizer.events.store'), mapEventPayload($category, [
        'latitude' => '',
        'longitude' => '',
    ]))->assertRedirect()->assertSessionHasNoErrors();

    expect(Event::firstWhere('name', 'Tripoli Street Food Night')->hasCoordinates())->toBeFalse();
});

it('refuses half a coordinate pair', function (string $field, string $value) {
    $organizer = User::factory()->organizer()->create();
    $category = Category::factory()->create();

    $this->actingAs($organizer)
        ->post(route('organizer.events.store'), mapEventPayload($category, [$field => $value]))
        ->assertSessionHasErrors();

    expect(Event::count())->toBe(0);
})->with([
    'latitude without longitude' => ['latitude', '32.8872'],
    'longitude without latitude' => ['longitude', '13.1913'],
]);

it('refuses coordinates that are not on Earth', function (string $latitude, string $longitude) {
    $organizer = User::factory()->organizer()->create();
    $category = Category::factory()->create();

    $this->actingAs($organizer)
        ->post(route('organizer.events.store'), mapEventPayload($category, [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]))
        ->assertSessionHasErrors();

    expect(Event::count())->toBe(0);
})->with([
    'latitude past the pole' => ['91', '13.1913'],
    'latitude past the south pole' => ['-90.5', '13.1913'],
    'longitude past the date line' => ['32.8872', '181'],
    'not a number' => ['here', 'there'],
]);

/*
|--------------------------------------------------------------------------
| Showing the map
|--------------------------------------------------------------------------
*/

it('shows the map on an event that has a pin', function () {
    $event = Event::factory()->create([
        'latitude' => 32.8872,
        'longitude' => 13.1913,
    ]);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertSee('data-map-view', false)
        ->assertSee('Getting there');
});

it('omits the map entirely when an event has no pin', function () {
    $event = Event::factory()->create(['latitude' => null, 'longitude' => null]);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertDontSee('data-map-view', false)
        ->assertDontSee('Getting there');
});

/*
|--------------------------------------------------------------------------
| Address lookup
|--------------------------------------------------------------------------
*/

it('keeps the address lookup behind authentication', function () {
    Http::fake();

    // A redirect rather than a 401: the app only renders JSON errors for api/*
    // routes, so an unauthenticated visitor is sent to the login page. What
    // matters here is that nothing reaches the upstream provider.
    $this->getJson(route('geocode', ['q' => 'Tripoli']))->assertRedirect(route('login'));

    Http::assertNothingSent();
});

it('returns matches to a signed-in organizer', function () {
    nominatimReturns([
        ['display_name' => 'Tripoli, Libya', 'lat' => '32.8872', 'lon' => '13.1913'],
    ]);

    $this->actingAs(User::factory()->organizer()->create())
        ->getJson(route('geocode', ['q' => 'Tripoli']))
        ->assertOk()
        ->assertJson([
            ['label' => 'Tripoli, Libya', 'latitude' => 32.8872, 'longitude' => 13.1913],
        ]);
});

it('identifies itself to the provider and biases towards Libya', function () {
    nominatimReturns([]);

    $this->actingAs(User::factory()->organizer()->create())
        ->getJson(route('geocode', ['q' => 'Tripoli']));

    // Both are Nominatim usage-policy obligations, not cosmetics.
    Http::assertSent(function ($request) {
        return str_contains($request->header('User-Agent')[0], 'events platform')
            && $request['viewbox'] === '9.3,33.2,25.2,19.5';
    });
});

it('asks the provider only once for a repeated search', function () {
    nominatimReturns([
        ['display_name' => 'Benghazi, Libya', 'lat' => '32.1167', 'lon' => '20.0667'],
    ]);

    $organizer = User::factory()->organizer()->create();

    $this->actingAs($organizer)->getJson(route('geocode', ['q' => 'Benghazi']))->assertOk();
    $this->actingAs($organizer)->getJson(route('geocode', ['q' => 'Benghazi']))->assertOk();

    Http::assertSentCount(1);
});

it('says the lookup is unavailable rather than pretending there are no matches', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response('upstream is down', 500),
    ]);

    $this->actingAs(User::factory()->organizer()->create())
        ->getJson(route('geocode', ['q' => 'Tripoli']))
        ->assertStatus(503);
});

it('rejects a query too short to be worth sending upstream', function () {
    Http::fake();

    $this->actingAs(User::factory()->organizer()->create())
        ->getJson(route('geocode', ['q' => 'ab']))
        ->assertStatus(422);

    Http::assertNothingSent();
});
