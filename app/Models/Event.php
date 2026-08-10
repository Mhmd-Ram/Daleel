<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'name', 'description', 'location', 'start_date_time', 'end_date_time',
    'tiket_cost', 'max_capacity', 'is_active', 'category_id', 'admin_id',
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
     * The category this event belongs to.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The admin who owns this event.
     *
     * @return BelongsTo<Admin, $this>
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
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
