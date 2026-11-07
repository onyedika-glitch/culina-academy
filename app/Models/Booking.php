<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $table = 'bookings';

    protected $fillable = [
        'course_id', 'intake_date', 'first_name', 'last_name', 'email', 'phone',
        'address', 'city', 'postcode', 'country', 'passport_country', 'visa',
        'notes', 'status', 'stripe_checkout_session_id', 'stripe_payment_intent_id',
        'deposit_amount_cents', 'currency', 'invite_token', 'invite_expires_at',
        'locale', 'page_url',
    ];

    protected $casts = [
        'invite_expires_at' => 'datetime',
        'deposit_amount_cents' => 'integer',
    ];

    // ---- Relationships ----

    public function course(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'course_id', 'ID');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BookingDocument::class);
    }

    // ---- Scopes ----

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeForCourse($query, int $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeForIntake($query, int $courseId, ?string $intakeDate = null)
    {
        $query->where('course_id', $courseId);
        if ($intakeDate) {
            $query->where('intake_date', $intakeDate);
        }
        return $query;
    }

    // ---- Helpers ----

    public function fullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function depositFormatted(): string
    {
        $amount = $this->deposit_amount_cents / 100;
        $symbol = strtoupper($this->currency ?? 'gbp') === 'GBP' ? '£' : strtoupper($this->currency ?? '');
        return $symbol . number_format($amount, 2);
    }

    /**
     * Count confirmed bookings for a given course/intake.
     */
    public static function confirmedCount(int $courseId, ?string $intakeDate = null): int
    {
        $query = static::where('course_id', $courseId)->where('status', 'confirmed');
        if ($intakeDate) {
            $query->where('intake_date', $intakeDate);
        }
        return $query->count();
    }
}
