<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Waitlist extends Model
{
    protected $table = 'ca_waitlist';
    protected $fillable = [
        'course_id', 'first_name', 'last_name', 'email', 'phone', 'notes',
        'intake_date', 'locale', 'page_url',
    ];
}