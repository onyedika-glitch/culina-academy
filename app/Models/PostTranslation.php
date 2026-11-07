<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTranslation extends Model
{
    protected $table = 'post_translations';
    protected $fillable = ['post_id', 'language_id', 'translation_group'];
    public $timestamps = false;
}