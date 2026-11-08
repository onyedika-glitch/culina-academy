<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $table = 'ca_enrollments';

    protected $fillable = ['intake_id', 'first_name', 'last_name', 'email', 'phone', 'notes', 'status'];
}
