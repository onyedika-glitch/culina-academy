<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $table = 'wp_options';
    protected $fillable = ['option_name', 'option_value', 'autoload'];
    public $timestamps = false;
}