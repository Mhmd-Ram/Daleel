<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The admin's view of the user base (SRS UC12 - UC14).
 *
 * Admins live in their own table behind their own guard, so nothing here can
 * reach an administrator account: every query only ever touches `users`.
 */
class UserController extends Controller
{
    /**
     * The user list, filterable by role and status (FR-10.1, FR-10.2).
     *
     * Like the public listing, an unrecognised filter value is ignored rather
     * than rejected: a mangled query string still renders the page.
     */
    public function index(Request $request): View
    {
        $role = UserRole::tryFrom((string) $request->query('role', ''));
        $status = $this->status($request);
        $keyword = $this->keyword($request);

        $users = User::query()
            ->withCount(['registrations', 'organizedEvents'])
            ->when($role, fn ($query, $value) => $query->where('role', $value))
            ->when($status, fn ($query, $value) => $query->where('is_banned', $value === 'banned'))
            ->when($keyword, function ($query, $term) {
                // LOWER(...) LIKE, not ILIKE: Postgres in the app, SQLite in tests.
                $pattern = '%'.mb_strtolower($term).'%';

                $query->where(fn ($query) => $query
                    ->whereRaw('LOWER(name) LIKE ?', [$pattern])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$pattern]));
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => UserRole::cases(),
            'selectedRole' => $role,
            'selectedStatus' => $status,
            'keyword' => $keyword,
        ]);
    }

    /**
     * Ban a user (FR-10.3, FR-10.4). They are blocked at the login screen and
     * evicted from any session they still hold.
     */
    public function ban(User $user): RedirectResponse
    {
        abort_if($user->isBanned(), 409, 'This account is already banned.');

        $user->forceFill(['is_banned' => true])->save();

        return back()->with('success', $user->name.' has been banned.');
    }

    /**
     * Lift a ban. Not in the SRS, but a ban with no undo is an operational trap:
     * the only alternative would be editing the database by hand.
     */
    public function unban(User $user): RedirectResponse
    {
        abort_unless($user->isBanned(), 409, 'This account is not banned.');

        $user->forceFill(['is_banned' => false])->save();

        return back()->with('success', $user->name.' can sign in again.');
    }

    /**
     * Demote an organizer back to attendee (FR-10.5, FR-10.6).
     *
     * Their events stay published and stay theirs: the use case says the user
     * "keeps their account", and silently unpublishing events people have
     * already saved would punish the attendees rather than the organizer. An
     * admin who wants the events gone removes them from the events screen.
     */
    public function revertRole(User $user): RedirectResponse
    {
        abort_unless($user->isOrganizer(), 409, 'This user is not an organizer.');

        $user->forceFill(['role' => UserRole::Attendee])->save();

        return back()->with('success', $user->name.' is an attendee again.');
    }

    /**
     * The chosen status filter, or null when it is absent or not one of ours.
     */
    private function status(Request $request): ?string
    {
        $status = (string) $request->query('status', '');

        return in_array($status, ['active', 'banned'], true) ? $status : null;
    }

    /**
     * The search term, or null when it is too short to narrow anything usefully.
     */
    private function keyword(Request $request): ?string
    {
        $term = trim((string) $request->query('q', ''));

        return mb_strlen($term) >= 2 ? mb_substr($term, 0, 120) : null;
    }
}
