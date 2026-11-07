<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $table = 'languages';
    protected $fillable = ['code', 'locale', 'name', 'flag_code', 'rtl', 'term_id'];
    public $timestamps = false;
}