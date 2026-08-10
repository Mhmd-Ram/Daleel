<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // A known admin to sign in with at /admin/login.
        $admin = Admin::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Site Admin', 'password' => Hash::make('password')],
        );

        // A known user to sign in with.
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'phone_number' => '+15551234567',
                'dob' => '1995-05-20',
                'location' => 'Tripoli',
                'password' => Hash::make('password'),
            ],
        );

        // Categories from the brief.
        $categories = collect(['Music', 'Tech', 'Sports', 'Arts', 'Business'])
            ->map(fn (string $name) => Category::firstOrCreate(['name' => $name]));

        // A spread of published events across categories owned by the admin.
        Event::factory()
            ->count(12)
            ->recycle([$admin, ...$categories])
            ->create();

        // A couple of inactive and past events to exercise the rules.
        Event::factory()->count(2)->inactive()->recycle([$admin, ...$categories])->create();
        Event::factory()->count(2)->past()->recycle([$admin, ...$categories])->create();

        // Register the test user for a few upcoming events.
        Event::where('is_active', true)
            ->where('end_date_time', '>', now())
            ->take(3)
            ->get()
            ->each(fn (Event $event) => $event->registeredUsers()->syncWithoutDetaching([
                $user->id => ['created_at' => now()],
            ]));
    }
}
