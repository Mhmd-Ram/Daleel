<?php

namespace App\Models;

use App\Enums\LibyanCity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use LogicException;

#[Fillable([
    'name', 'description', 'location', 'city', 'latitude', 'longitude',
    'start_date_time', 'end_date_time', 'tiket_cost', 'max_capacity',
    'is_active', 'category_id', 'admin_id', 'organizer_id',
])]
class Event extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date_time' => 'datetime',
            'end_date_time' => 'datetime',
            'tiket_cost' => 'decimal:2',
            'is_active' => 'boolean',
            // Casting to the enum means an unrecognised city throws on write
            // rather than reaching the database, so the filter dropdown can
            // trust every stored value. Legacy rows stay null.
            'city' => LibyanCity::class,
            // Float rather than decimal: the map needs JSON numbers, and a
            // decimal cast hands back strings that Leaflet will not accept.
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Guard the ownership invariant: an event belongs to an admin or to an
     * organizer, never to both and never to neither.
     */
    protected static function booted(): void
    {
        static::saving(function (Event $event): void {
            if (($event->admin_id === null) === ($event->organizer_id === null)) {
                throw new LogicException(
                    'An event must be owned by exactly one of an admin or an organizer.'
                );
            }
        });

        static::deleting(function (Event $event): void {
            // Soft deleting never reaches the database DELETE, so the reports
            // foreign key cascade does not fire. Clearing them here keeps both
            // delete paths identical and, more importantly, keeps the admin
            // moderation queue from holding rows whose event it can no longer
            // resolve.
            $event->reports()->delete();
        });

        static::forceDeleted(function (Event $event): void {
            // Only on a force delete. A soft-deleted event can be restored, and
            // deleting its cover here would bring it back without one.
            if ($event->image_path) {
                Storage::disk('public')->delete($event->image_path);
            }
        });
    }

    /**
     * A URL for this event's cover image at roughly the given size.
     *
     * An uploaded cover is served at its own size and cropped by CSS; without
     * one we fall back to the seeded placeholder the app has always shown, so
     * every event still has artwork. The dimensions only steer the fallback.
     */
    public function imageUrl(int $width = 800, int $height = 500): string
    {
        if ($this->image_path) {
            // `asset()` rather than the disk's own url(): the disk builds from
            // APP_URL, so any mismatch (a dev server on another port, a staging
            // host) serves 404s. `asset()` follows the current request, and is
            // what the rest of the app already uses for public files.
            return asset('storage/'.$this->image_path);
        }

        return "https://picsum.photos/seed/event-{$this->id}/{$width}/{$height}";
    }

    /**
     * The category this event belongs to.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The admin who owns this event, when it was created from the admin area.
     *
     * @return BelongsTo<Admin, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * The organizer who owns this event, when it was created by a user.
     *
     * @return BelongsTo<User, $this>
     */
    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * The event's creator. The saving guard below means exactly one of the two
     * owner columns is set, so this never comes back empty.
     */
    public function owner(): Admin|User
    {
        return $this->admin_id !== null ? $this->admin : $this->organizer;
    }

    /**
     * Whether the given organizer created this event.
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->organizer_id !== null && $this->organizer_id === $user->id;
    }

    /**
     * The users registered to attend this event.
     *
     * @return BelongsToMany<User, $this>
     */
    public function registeredUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_regestrations')
            ->withPivot('created_at', 'reminder_sent');
    }

    /**
     * The reports attendees have filed against this event.
     *
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Whether this event has a map pin.
     *
     * Coordinates are stored as a pair or not at all, but this checks both
     * columns so a half-written row can never reach the map as a (0, lng) pin
     * off the coast of Africa.
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Whether the event's end date/time is in the past.
     */
    public function hasFinished(): bool
    {
        return $this->end_date_time->isPast();
    }

    /**
     * Whether the event has reached its capacity limit (if any).
     */
    public function isFull(): bool
    {
        return $this->max_capacity !== null
            && $this->registeredUsers()->count() >= $this->max_capacity;
    }
}
