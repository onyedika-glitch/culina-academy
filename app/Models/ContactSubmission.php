<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    protected $table = 'ca_contact_submissions';

    protected $fillable = [
        'name', 'email', 'phone', 'message', 'locale', 'page_url',
        'cloudinary_public_id', 'cloudinary_url', 'original_file_name', 'file_mime_type', 'file_size_bytes',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
    ];
}
