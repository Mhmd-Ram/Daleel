<?php

namespace App\Models;

use App\Enums\LibyanCity;
use App\Enums\OrganizerApplicationStatus;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// `role` is deliberately not fillable: it is only ever set by an admin
// approving an organizer application.
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
            ->withPivot('created_at');
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
     * Whether this user may create and manage their own events.
     */
    public function isOrganizer(): bool
    {
        return $this->role === UserRole::Organizer;
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
