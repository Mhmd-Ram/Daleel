<?php

namespace App\Models;

use App\Enums\LibyanCity;
use App\Enums\OrganizerApplicationStatus;
use App\Enums\UserRole;
use App\Notifications\QueuedVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// `role` and `is_banned` are deliberately not fillable: they are only ever set
// by an admin, approving an organizer application or blocking an account.
#[Fillable(['name', 'email', 'phone_number', 'dob', 'location', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'dob' => 'date',
            'location' => LibyanCity::class,
            'role' => UserRole::class,
            'is_banned' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * The events this user has registered to attend.
     *
     * @return BelongsToMany<Event, $this>
     */
    public function registrations(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'user_regestrations')
            ->withPivot('created_at', 'reminder_sent');
    }

    /**
     * The events this user organizes.
     *
     * @return HasMany<Event, $this>
     */
    public function organizedEvents(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /**
     * This user's applications to become an organizer, newest first.
     *
     * @return HasMany<OrganizerApplication, $this>
     */
    public function organizerApplications(): HasMany
    {
        return $this->hasMany(OrganizerApplication::class)->latest();
    }

    /**
     * The reports this user has filed against events.
     *
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'attendee_id');
    }

    /**
     * Real people, excluding the accounts that exist only so an admin can
     * browse the public site.
     *
     * A local scope rather than a global one on purpose: a global scope
     * would also filter the auth provider's lookups and make those accounts
     * impossible to sign in as, which is the one thing they exist for.
     *
     * @param  Builder<User>  $query
     */
    public function scopeReal(Builder $query): void
    {
        $query->whereNull('staff_admin_id');
    }

    /**
     * Whether this account exists only to give an admin a presence on the
     * public site, rather than belonging to a person who signed up.
     */
    public function isStaffAccount(): bool
    {
        return $this->staff_admin_id !== null;
    }

    /**
     * Whether this user may create and manage their own events.
     */
    public function isOrganizer(): bool
    {
        return $this->role === UserRole::Organizer;
    }

    /**
     * Whether an admin has blocked this account from signing in.
     */
    public function isBanned(): bool
    {
        return $this->is_banned;
    }

    /**
     * Send the verification email through the queue rather than inline.
     *
     * The stock notification sends synchronously, which puts an SMTP
     * round-trip inside the registration request - slow against Gmail, and
     * a 500 on the user's first action if the mail server is unreachable.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmail);
    }

    /**
     * One or two letters standing in for an avatar in the site header.
     *
     * First and last word rather than the first two, so "Amal bint Yusuf"
     * reads AY. Multibyte throughout: Arabic names must not be sliced by byte.
     */
    public function initials(): string
    {
        $words = preg_split('/\s+/u', trim($this->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === []) {
            return '?';
        }

        $initials = mb_substr($words[0], 0, 1)
            .(count($words) > 1 ? mb_substr((string) end($words), 0, 1) : '');

        return mb_strtoupper($initials);
    }

    /**
     * The application currently awaiting an admin decision, if any.
     */
    public function pendingOrganizerApplication(): ?OrganizerApplication
    {
        return $this->organizerApplications()
            ->where('status', OrganizerApplicationStatus::Pending)
            ->first();
    }
}
