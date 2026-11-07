<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';
    protected $fillable = ['seoable_id', 'seoable_type', 'title', 'description', 'canonical_url', 'robots', 'og_data', 'schema_data', 'focus_keyword'];
    protected $casts = ['og_data' => 'array', 'schema_data' => 'array'];
}