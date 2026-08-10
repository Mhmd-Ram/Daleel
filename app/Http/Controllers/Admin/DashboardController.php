<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrganizerApplication;
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
            'stats' => [
                'events' => Event::count(),
                'published' => Event::where('is_active', true)->count(),
                'drafts' => Event::where('is_active', false)->count(),
                'upcoming' => Event::where('is_active', true)
                    ->where('end_date_time', '>', now())
                    ->count(),
                'registrations' => DB::table('user_regestrations')->count(),
                'users' => User::count(),
                'organizers' => User::where('role', UserRole::Organizer)->count(),
                'pendingApplications' => OrganizerApplication::pending()->count(),
            ],
            'recentEvents' => Event::with(['category', 'admin', 'organizer'])
                ->withCount('registeredUsers')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
