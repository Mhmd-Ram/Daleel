<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use LogicException;

#[Fillable([
    'name', 'description', 'location', 'start_date_time', 'end_date_time',
    'tiket_cost', 'max_capacity', 'is_active', 'category_id', 'admin_id',
    'organizer_id',
])]
class Event extends Model
{
    use HasFactory;

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
     * The event's creator: exactly one of the admin or the organizer.
     */
    public function owner(): Admin|User|null
    {
        return $this->admin_id !== null ? $this->admin : $this->organizer;
    }

    /**
     * The creator's name, for listings that mix both kinds of owner.
     */
    public function ownerName(): string
    {
        return $this->owner()?->name ?? 'Unknown';
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
            ->withPivot('created_at');
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
