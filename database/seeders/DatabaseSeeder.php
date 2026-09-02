<?php

namespace Database\Seeders;

use App\Enums\LibyanCity;
use App\Enums\ReportReason;
use App\Enums\UserRole;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Event;
use App\Models\Report;
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

        // A known user to sign in with. Pre-verified so the verification gate
        // does not block a fresh local checkout.
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'phone_number' => '+15551234567',
                'dob' => '1995-05-20',
                'location' => LibyanCity::Tripoli,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        // An approved organizer, to exercise the organizer area.
        $organizer = User::firstOrCreate(
            ['email' => 'organizer@example.com'],
            [
                'name' => 'Amal Zarrouk',
                'phone_number' => '+218911234567',
                'dob' => '1990-02-11',
                'location' => LibyanCity::Benghazi,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );
        $organizer->forceFill(['role' => UserRole::Organizer])->save();

        // An attendee with an application still waiting in the admin queue.
        $applicant = User::firstOrCreate(
            ['email' => 'applicant@example.com'],
            [
                'name' => 'Yusra Ben Khalifa',
                'phone_number' => '+218921234567',
                'dob' => '1998-09-03',
                'location' => LibyanCity::Misrata,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        if ($applicant->pendingOrganizerApplication() === null && ! $applicant->isOrganizer()) {
            $applicant->organizerApplications()->create([
                'message' => 'I run a monthly meetup for web developers in Misrata and would like to list it here.',
            ]);
        }

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

        // Events owned by the organizer rather than the admin.
        Event::factory()
            ->count(3)
            ->organizedBy($organizer)
            ->recycle($categories)
            ->create();

        // Register the test user for a few upcoming events.
        Event::where('is_active', true)
            ->where('end_date_time', '>', now())
            ->take(3)
            ->get()
            ->each(fn (Event $event) => $event->registeredUsers()->syncWithoutDetaching([
                $user->id => ['created_at' => now()],
            ]));

        // A couple of reports so the admin queue is not empty on a fresh
        // checkout. Only `reason` is fillable on Report, so firstOrCreate would
        // silently drop both foreign keys; the pair is set explicitly instead,
        // and the existence check keeps the seeder re-runnable.
        Event::where('is_active', true)
            ->where('end_date_time', '>', now())
            ->take(2)
            ->get()
            ->each(function (Event $event, int $index) use ($user, $applicant) {
                $reporter = $index === 0 ? $user : $applicant;

                if ($event->reports()->where('attendee_id', $reporter->id)->exists()) {
                    return;
                }

                $report = new Report([
                    'reason' => $index === 0 ? ReportReason::Spam : ReportReason::MisleadingDescription,
                ]);
                $report->attendee_id = $reporter->id;

                $event->reports()->save($report);
            });
    }
}
