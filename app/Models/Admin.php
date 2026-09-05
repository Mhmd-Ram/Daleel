<?php

namespace App\Models;

use App\Enums\LibyanCity;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * The events created by this admin.
     *
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * The ordinary user account this admin browses the public site with.
     *
     * @return HasOne<User, $this>
     */
    public function siteUser(): HasOne
    {
        return $this->hasOne(User::class, 'staff_admin_id');
    }

    /**
     * That account, created on first use.
     *
     * Admins live on their own guard with no user row, so the public site sees
     * them as a guest and every foreign key that wants a `users.id` - saved
     * events, reports, organizer applications - has nothing to point at. Giving
     * them a real user row is what lets an admin do what a visitor can do.
     *
     * See siteUserEmail() for how the address is chosen; the row is always a
     * new one, never an existing account claimed by matching on email.
     */
    public function ensureSiteUser(): User
    {
        return $this->siteUser()->first() ?? $this->createSiteUser();
    }

    /**
     * Build this admin's site account.
     */
    private function createSiteUser(): User
    {
        $user = new User([
            'name' => $this->name,
            'email' => $this->siteUserEmail(),
            // `users.phone_number` is unique and not nullable. Real accounts are
            // +2189[1-5]-------, so this prefix cannot collide with one.
            'phone_number' => '+218900'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT),
            'dob' => now()->subYears(30)->toDateString(),
            'location' => LibyanCity::Tripoli,
            // Nobody knows this, and the app has no password reset, so the
            // account cannot be signed into through the normal login form.
            'password' => Str::random(64),
        ]);

        // Not fillable by design, so they are set rather than mass assigned.
        $user->staff_admin_id = $this->id;
        $user->role = UserRole::Attendee;
        $user->is_banned = false;
        // Public actions sit behind the `verified` middleware.
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    /**
     * The address for this admin's site account.
     *
     * Their own, so a confirmation raised while they are testing the site
     * actually reaches them rather than bouncing.
     *
     * When a real account already holds that address the synthetic form is used
     * instead: `users.email` is unique, and taking the existing row over would
     * let anyone who registers with the admin's address hand the admin their
     * account. `.invalid` is reserved (RFC 2606), so that fallback can never
     * reach a real inbox by accident.
     */
    private function siteUserEmail(): string
    {
        return User::where('email', $this->email)->exists()
            ? "admin-{$this->id}@staff.invalid"
            : $this->email;
    }
}
