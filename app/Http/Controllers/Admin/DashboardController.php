<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrganizerApplication;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Site-wide numbers for the admin landing page.
     */
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => $this->stats(),
            'recentEvents' => Event::with(['category', 'admin', 'organizer'])
                ->withCount('registeredUsers')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * The headline counts shown across the top of the dashboard.
     *
     * @return array<string, int>
     */
    private function stats(): array
    {
        return [
            'events' => Event::count(),
            'published' => Event::where('is_active', true)->count(),
            'drafts' => Event::where('is_active', false)->count(),
            'upcoming' => Event::where('is_active', true)
                ->where('end_date_time', '>', now())
                ->count(),
            'registrations' => DB::table('user_regestrations')->count(),
            'users' => User::real()->count(),
            'organizers' => User::real()->where('role', UserRole::Organizer)->count(),
            'bannedUsers' => User::real()->where('is_banned', true)->count(),
            'pendingApplications' => OrganizerApplication::pending()->count(),
            'reports' => Report::count(),
        ];
    }
}
