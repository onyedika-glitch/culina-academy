<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $table = 'menu_items';
    protected $fillable = ['menu_id', 'parent_id', 'post_id', 'title', 'url', 'target', 'classes', 'order'];
}