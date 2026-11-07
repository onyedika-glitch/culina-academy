<?php

namespace App\Services;

use App\Models\Post;
use App\Models\SeoMeta;
use Illuminate\Support\Facades\DB;

class SeoService
{
    /** @var array<string, string> */
    protected static array $rankMathKeys = [
        'title' => 'rank_math_title',
        'description' => 'rank_math_description',
        'focus_keyword' => 'rank_math_focus_keyword',
        'canonical_url' => 'rank_math_canonical_url',
        'robots' => 'rank_math_robots',
        'facebook_title' => 'rank_math_facebook_title',
        'facebook_description' => 'rank_math_facebook_description',
        'facebook_image' => 'rank_math_facebook_image',
        'facebook_image_id' => 'rank_math_facebook_image_id',
        'twitter_title' => 'rank_math_twitter_title',
        'twitter_description' => 'rank_math_twitter_description',
        'twitter_image' => 'rank_math_twitter_image',
        'twitter_image_id' => 'rank_math_twitter_image_id',
    ];

    public static function forPost(?int $postId): array
    {
        if (!$postId) {
            return static::defaults();
        }

        return CalCache::remember(
            "cal.seo.post.{$postId}",
            now()->addHour(),
            static fn () => static::buildPostSeo($postId),
        );
    }

    /** @return array<string, string> */
    protected static function buildPostSeo(int $postId): array
    {
        $post = Post::find($postId);
        if (!$post) {
            return static::defaults();
        }

        $meta = static::readRankMathMeta($postId);
        $laravelSeo = SeoMeta::where('seoable_type', Post::class)
            ->where('seoable_id', $postId)
            ->first();

        $title = $meta['title'] ?: ($laravelSeo?->title ?: $post->post_title);
        $description = $meta['description'] ?: ($laravelSeo?->description ?: strip_tags((string) $post->post_excerpt));
        $robots = $meta['robots'] ?: ($laravelSeo?->robots ?: 'index, follow');
        $canonical = $meta['canonical_url'] ?: ($laravelSeo?->canonical_url ?: PolylangService::buildPostUrl($postId));

        $ogImage = $meta['facebook_image']
            ?: ($meta['twitter_image']
                ?: ($meta['facebook_image_id'] ? AcfFieldService::attachmentUrl((int) $meta['facebook_image_id']) : ''));

        // Default OG image if none found
        if (empty($ogImage)) {
            $logo = AcfFieldService::getOption('cal_header_logo');
            $ogImage = $logo ? AcfFieldService::imageUrl($logo) : '/uploads/2026/05/cropped-logob-180x180.png';
        }

        return [
            'title' => $title,
            'description' => $description,
            'focus_keyword' => $meta['focus_keyword'] ?: ($laravelSeo?->focus_keyword ?? ''),
            'robots' => is_array($robots) ? implode(', ', $robots) : (string) $robots,
            'canonical_url' => $canonical,
            'og_title' => $meta['facebook_title'] ?: $title,
            'og_description' => $meta['facebook_description'] ?: $description,
            'og_image' => $ogImage,
            'twitter_title' => $meta['twitter_title'] ?: $title,
            'twitter_description' => $meta['twitter_description'] ?: $description,
            'twitter_image' => $meta['twitter_image'] ?: $ogImage,
        ];
    }

    /** @return array<string, mixed> */
    public static function getEditableMeta(int $postId): array
    {
        $meta = static::readRankMathMeta($postId);
        $laravelSeo = SeoMeta::where('seoable_type', Post::class)
            ->where('seoable_id', $postId)
            ->first();

        return [
            'title' => $meta['title'] ?: ($laravelSeo?->title ?? ''),
            'description' => $meta['description'] ?: ($laravelSeo?->description ?? ''),
            'focus_keyword' => $meta['focus_keyword'] ?: ($laravelSeo?->focus_keyword ?? ''),
            'canonical_url' => $meta['canonical_url'] ?: ($laravelSeo?->canonical_url ?? ''),
            'robots' => is_array($meta['robots'] ?? null)
                ? implode(', ', $meta['robots'])
                : (string) ($meta['robots'] ?: ($laravelSeo?->robots ?? 'index, follow')),
            'facebook_title' => $meta['facebook_title'] ?? '',
            'facebook_description' => $meta['facebook_description'] ?? '',
            'facebook_image' => $meta['facebook_image'] ?? '',
            'facebook_image_id' => $meta['facebook_image_id'] ?? '',
            'twitter_title' => $meta['twitter_title'] ?? '',
            'twitter_description' => $meta['twitter_description'] ?? '',
            'twitter_image' => $meta['twitter_image'] ?? '',
            'twitter_image_id' => $meta['twitter_image_id'] ?? '',
        ];
    }

    /** @param array<string, mixed> $data */
    public static function saveForPost(int $postId, array $data): void
    {
        foreach (static::$rankMathKeys as $field => $metaKey) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $value = $data[$field];
            if ($field === 'robots' && is_string($value)) {
                $value = array_map('trim', explode(',', $value));
            }
            PostMetaWriter::set($postId, $metaKey, $value);
        }

        SeoMeta::updateOrCreate(
            ['seoable_type' => Post::class, 'seoable_id' => $postId],
            [
                'title' => $data['title'] ?? '',
                'description' => $data['description'] ?? '',
                'focus_keyword' => $data['focus_keyword'] ?? '',
                'canonical_url' => $data['canonical_url'] ?? '',
                'robots' => $data['robots'] ?? 'index, follow',
            ],
        );

        SiteCacheService::clearSeo($postId);
    }

    /** @return array<string, mixed> */
    protected static function readRankMathMeta(int $postId): array
    {
        $keys = array_values(static::$rankMathKeys);
        $rows = DB::table('wp_postmeta')
            ->where('post_id', $postId)
            ->whereIn('meta_key', $keys)
            ->pluck('meta_value', 'meta_key');

        $result = [];
        foreach (static::$rankMathKeys as $field => $metaKey) {
            $raw = $rows[$metaKey] ?? '';
            $result[$field] = AcfFieldService::parseValuePublic($raw);
        }

        return $result;
    }

    /** @return array<string, string> */
    protected static function defaults(): array
    {
        return [
            'title' => 'The Culina Academy — Professional Chef Courses & Culinary Training',
            'description' => 'The Culina Academy offers professional chef courses, culinary training, and hospitality programmes. Start your culinary career with expert training in Your City.',
            'focus_keyword' => 'professional chef courses Your City, culinary training, cooking academy',
            'robots' => 'index, follow',
            'canonical_url' => url('/'),
            'og_title' => 'The Culina Academy — Professional Chef Courses',
            'og_description' => 'Professional chef courses, culinary training, and hospitality programmes in Your City. Start your culinary career at The Culina Academy.',
            'og_image' => '',
            'twitter_title' => 'The Culina Academy — Professional Chef Courses',
            'twitter_description' => 'Professional chef courses, culinary training, and hospitality programmes in Your City.',
            'twitter_image' => '',
        ];
    }
}