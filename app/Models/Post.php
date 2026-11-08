<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model
{
    protected $table = 'wp_posts';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * wp_posts has no created_at/updated_at; WordPress records these times in
     * post_date and post_modified, so map Eloquent's timestamps onto them.
     */
    public const CREATED_AT = 'post_date';

    public const UPDATED_AT = 'post_modified';

    protected $fillable = [
        'ID', 'author_id', 'post_date', 'post_date_gmt', 'post_content', 'post_title',
        'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_password',
        'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt',
        'post_content_filtered', 'parent_id', 'guid', 'menu_order', 'post_type',
        'post_mime_type', 'comment_count',
    ];

    protected $casts = [
        'post_date' => 'datetime',
        'post_date_gmt' => 'datetime',
        'post_modified' => 'datetime',
        'post_modified_gmt' => 'datetime',
    ];

    public function meta(): HasMany
    {
        return $this->hasMany(PostMeta::class, 'post_id', 'ID');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function seo(): MorphMany
    {
        return $this->morphMany(SeoMeta::class, 'seoable');
    }

    public function translation(): HasMany
    {
        return $this->hasMany(PostTranslation::class, 'post_id');
    }

    public function getMeta(string $key, mixed $default = null): mixed
    {
        $meta = $this->meta->firstWhere('meta_key', $key);
        return $meta ? $meta->meta_value : $default;
    }

    public function scopePublished($query)
    {
        return $query->where('post_status', 'publish');
    }

    public function scopeType($query, string $type)
    {
        return $query->where('post_type', $type);
    }

    public function scopeTemplate($query, string $template)
    {
        return $query->whereHas('meta', fn($q) =>
            $q->where('meta_key', '_wp_page_template')->where('meta_value', $template)
        );
    }

    public function scopeForLanguage($query, ?string $lang = null)
    {
        $lang = $lang ?: \App\Services\PolylangService::currentLanguage();
        return $query->whereExists(function ($q) use ($lang) {
            $q->select(\Illuminate\Support\Facades\DB::raw(1))
                ->from('wp_term_relationships')
                ->join('wp_term_taxonomy', 'wp_term_relationships.term_taxonomy_id', '=', 'wp_term_taxonomy.term_taxonomy_id')
                ->join('wp_terms', 'wp_term_taxonomy.term_id', '=', 'wp_terms.term_id')
                ->whereColumn('wp_term_relationships.object_id', 'wp_posts.ID')
                ->where('wp_term_taxonomy.taxonomy', 'language')
                ->where('wp_terms.slug', $lang);
        });
    }
}
