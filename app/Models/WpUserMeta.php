<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WpUserMeta extends Model
{
    protected $table = 'wp_usermeta';

    protected $primaryKey = 'umeta_id';

    public $timestamps = false;

    protected $fillable = ['user_id', 'meta_key', 'meta_value'];
}