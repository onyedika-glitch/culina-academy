<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $table = 'ca_applications';

    protected $fillable = [
        'first_name', 'surname', 'email', 'phone', 'country', 'intl_code',
        'address', 'city', 'postcode', 'passport_country', 'visa', 'heard',
        'start_year', 'start_month', 'cookery_ability', 'motivation', 'courses',
        'course_id', 'intake_date', 'locale', 'page_url', 'status',
    ];

    protected $casts = [
        'courses' => 'array',
    ];
}