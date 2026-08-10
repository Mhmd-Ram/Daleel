<?php

namespace App\Models;

use App\Enums\OrganizerApplicationStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * A user's request to be promoted from attendee to organizer.
 */
#[Fillable(['message'])]
class OrganizerApplication extends Model
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
            'status' => OrganizerApplicationStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * The user who applied.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The admin who decided on this application, once reviewed.
     *
     * @return BelongsTo<Admin, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    /**
     * Limit the query to applications still awaiting a decision.
     *
     * @param  Builder<$this>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', OrganizerApplicationStatus::Pending);
    }

    /**
     * Whether this application is still awaiting a decision.
     */
    public function isPending(): bool
    {
        return $this->status === OrganizerApplicationStatus::Pending;
    }

    /**
     * Approve the application and promote the applicant to organizer.
     */
    public function approve(Admin $reviewer): void
    {
        DB::transaction(function () use ($reviewer) {
            $this->markReviewed(OrganizerApplicationStatus::Approved, $reviewer);

            $this->user->forceFill(['role' => UserRole::Organizer])->save();
        });
    }

    /**
     * Reject the application. The user keeps their role and may apply again.
     */
    public function reject(Admin $reviewer): void
    {
        $this->markReviewed(OrganizerApplicationStatus::Rejected, $reviewer);
    }

    /**
     * Record the decision and who made it.
     */
    private function markReviewed(OrganizerApplicationStatus $status, Admin $reviewer): void
    {
        $this->forceFill([
            'status' => $status,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ])->save();
    }
}
