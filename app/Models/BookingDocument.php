<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingDocument extends Model
{
    protected $table = 'booking_documents';

    protected $fillable = [
        'booking_id', 'document_type', 'original_name', 'hashed_name',
        'cloudinary_public_id', 'cloudinary_url', 'mime_type', 'size_bytes',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
