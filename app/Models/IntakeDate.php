<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntakeDate extends Model
{
    protected $table = 'ca_intake_dates';

    protected $fillable = [
        'course_id', 'intake_date', 'status', 'max_students', 'enrolled_count',
        'availability_override', 'admin_notes',
    ];

    protected $casts = [
        'intake_date' => 'date',
        'max_students' => 'integer',
        'enrolled_count' => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'course_id', 'ID');
    }

    /**
     * Check if this intake has available slots, respecting admin overrides.
     *
     * Priority:
     *   1. Admin override (force_available / force_full) — always wins
     *   2. If status is not 'available' — treat as unavailable
     *   3. Otherwise compare enrolled_count + confirmed bookings vs max_students
     */
    public function isAvailable(): bool
    {
        // Admin override takes absolute priority
        if ($this->availability_override === 'force_available') {
            return true;
        }
        if ($this->availability_override === 'force_full') {
            return false;
        }

        // If the intake is marked as a non-available status
        if ($this->status !== 'available') {
            return false;
        }

        // Automatic: check actual capacity
        $totalBooked = $this->enrolled_count + Booking::confirmedCount(
            $this->course_id,
            $this->intake_date?->format('Y-m-d')
        );

        return $totalBooked < $this->max_students;
    }

    /**
     * Get the effective availability status label for display.
     *
     * Returns: 'available', 'few', or 'full'
     */
    public function effectiveStatus(): string
    {
        if ($this->availability_override === 'force_available') {
            return 'available';
        }
        if ($this->availability_override === 'force_full') {
            return 'full';
        }

        if ($this->status !== 'available') {
            return 'full';
        }

        $totalBooked = $this->enrolled_count + Booking::confirmedCount(
            $this->course_id,
            $this->intake_date?->format('Y-m-d')
        );

        if ($totalBooked >= $this->max_students) {
            return 'full';
        }

        $remaining = $this->max_students - $totalBooked;
        $threshold = max(2, (int) round($this->max_students * 0.2)); // 20% or at least 2

        return $remaining <= $threshold ? 'few' : 'available';
    }

    /**
     * Get the override label for admin display.
     */
    public function overrideLabel(): string
    {
        return match ($this->availability_override) {
            'force_available' => '✅ Forced Available',
            'force_full' => '🚫 Forced Full',
            default => '⚙️ Automatic',
        };
    }

    /**
     * Get remaining capacity (actual, ignoring overrides).
     */
    public function remainingSpots(): int
    {
        $totalBooked = $this->enrolled_count + Booking::confirmedCount(
            $this->course_id,
            $this->intake_date?->format('Y-m-d')
        );

        return max(0, $this->max_students - $totalBooked);
    }
}
