<?php

use App\Models\Admin;
use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Cover images are handled by one trait shared by the admin and organizer
 * controllers, so these exercise both entry points against the same rules.
 */
beforeEach(function () {
    Storage::fake('public');
});

/** A valid cover, sized past the `dimensions` rule. */
function coverImage(string $name = 'cover.jpg'): UploadedFile
{
    return UploadedFile::fake()->image($name, 800, 500);
}

function coverPayload(Category $category, array $overrides = []): array
{
    return array_merge([
        'name' => 'Tripoli Jazz Night',
        'description' => 'An evening of live jazz.',
        'location' => 'Old City',
        'city' => 'Tripoli',
        'category_id' => $category->id,
        'start_date_time' => now()->addWeek()->format('Y-m-d\TH:i'),
        'end_date_time' => now()->addWeek()->addHours(3)->format('Y-m-d\TH:i'),
        'tiket_cost' => 0,
        'is_active' => '1',
    ], $overrides);
}

it('stores a cover uploaded by an admin', function () {
    $category = Category::factory()->create();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->post(route('admin.events.store'), coverPayload($category, ['image' => coverImage()]))
        ->assertRedirect(route('admin.events.index'));

    $event = Event::firstWhere('name', 'Tripoli Jazz Night');

    expect($event->image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($event->image_path);
});

it('stores a cover uploaded by an organizer', function () {
    $organizer = User::factory()->organizer()->create();
    $category = Category::factory()->create();

    $this->actingAs($organizer)
        ->post(route('organizer.events.store'), coverPayload($category, ['image' => coverImage()]))
        ->assertRedirect(route('organizer.events.index'));

    expect(Event::firstWhere('name', 'Tripoli Jazz Night')->image_path)->not->toBeNull();
});

it('keeps the existing cover when an edit sends no new file', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $event = Event::factory()->create([
        'admin_id' => $admin->id,
        'organizer_id' => null,
        'category_id' => $category->id,
        'image_path' => 'events/original.jpg',
    ]);

    Storage::disk('public')->put('events/original.jpg', 'original');

    $this->actingAs($admin, 'admin')
        ->put(route('admin.events.update', $event), coverPayload($category, ['name' => 'Renamed']))
        ->assertRedirect(route('admin.events.index'));

    expect($event->fresh()->image_path)->toBe('events/original.jpg');
    Storage::disk('public')->assertExists('events/original.jpg');
});

it('deletes the old file when the cover is replaced', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $event = Event::factory()->create([
        'admin_id' => $admin->id,
        'organizer_id' => null,
        'category_id' => $category->id,
        'image_path' => 'events/original.jpg',
    ]);

    Storage::disk('public')->put('events/original.jpg', 'original');

    $this->actingAs($admin, 'admin')
        ->put(route('admin.events.update', $event), coverPayload($category, ['image' => coverImage('new.jpg')]));

    Storage::disk('public')->assertMissing('events/original.jpg');
    expect($event->fresh()->image_path)->not->toBe('events/original.jpg');
});

it('clears the cover when remove is ticked', function () {
    $admin = Admin::factory()->create();
    $category = Category::factory()->create();
    $event = Event::factory()->create([
        'admin_id' => $admin->id,
        'organizer_id' => null,
        'category_id' => $category->id,
        'image_path' => 'events/original.jpg',
    ]);

    Storage::disk('public')->put('events/original.jpg', 'original');

    $this->actingAs($admin, 'admin')
        ->put(route('admin.events.update', $event), coverPayload($category, ['remove_image' => '1']));

    expect($event->fresh()->image_path)->toBeNull();
    Storage::disk('public')->assertMissing('events/original.jpg');
});

it('rejects a file that is not an image', function () {
    $category = Category::factory()->create();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->post(route('admin.events.store'), coverPayload($category, [
            'image' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
        ]))
        ->assertSessionHasErrors('image');

    expect(Event::where('name', 'Tripoli Jazz Night')->exists())->toBeFalse();
});

it('rejects an image below the minimum dimensions', function () {
    $category = Category::factory()->create();

    $this->actingAs(Admin::factory()->create(), 'admin')
        ->post(route('admin.events.store'), coverPayload($category, [
            'image' => UploadedFile::fake()->image('tiny.jpg', 100, 60),
        ]))
        ->assertSessionHasErrors('image');
});

it('falls back to a placeholder when an event has no cover', function () {
    $event = Event::factory()->create(['image_path' => null]);

    expect($event->imageUrl())->toContain('picsum.photos');
});

it('serves the uploaded cover once one exists', function () {
    $event = Event::factory()->create(['image_path' => 'events/cover.jpg']);

    expect($event->imageUrl())
        ->toContain('events/cover.jpg')
        ->not->toContain('picsum.photos');
});

it('keeps the file when an event is only soft deleted', function () {
    $admin = Admin::factory()->create();
    $event = Event::factory()->create([
        'admin_id' => $admin->id,
        'organizer_id' => null,
        'image_path' => 'events/original.jpg',
    ]);

    Storage::disk('public')->put('events/original.jpg', 'original');

    $this->actingAs($admin, 'admin')->delete(route('admin.events.destroy', $event));

    // The event can still be restored, so its cover has to survive with it.
    Storage::disk('public')->assertExists('events/original.jpg');
});

it('deletes the file when an event is force deleted', function () {
    $event = Event::factory()->create(['image_path' => 'events/original.jpg']);
    Storage::disk('public')->put('events/original.jpg', 'original');

    $event->forceDelete();

    Storage::disk('public')->assertMissing('events/original.jpg');
});
