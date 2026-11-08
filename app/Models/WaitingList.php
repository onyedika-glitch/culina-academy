<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitingList extends Model
{
    protected $table = 'waiting_lists';

    protected $fillable = [
        'course_id', 'intake_date', 'first_name', 'last_name', 'email', 'phone',
        'status', 'invite_token', 'invite_token_expires_at', 'invited_at',
        'locale', 'page_url',
    ];

    protected $casts = [
        'invite_token_expires_at' => 'datetime',
        'invited_at' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'course_id', 'ID');
    }

    // ---- Scopes ----

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function scopeForCourse($query, int $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeChronological($query)
    {
        return $query->orderBy('created_at', 'asc');
    }

    // ---- Helpers ----

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    /**
     * Check if the invite token is still valid (not expired).
     */
    public function isInviteValid(): bool
    {
        return $this->invite_token
            && $this->invite_token_expires_at
            && $this->invite_token_expires_at->isFuture();
    }
}
