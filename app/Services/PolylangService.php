<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PolylangService
{
    protected static ?string $currentLanguage = null;

    protected static ?array $languagesCache = null;

    protected static array $postLanguageCache = [];

    protected static array $translationGroupCache = [];

    /** @var array<string, array<int, array{slug:string,name:string,url:string,active:bool,flag_code:?string}>> */
    protected static array $languageSwitcherCache = [];

    public static function setCurrentLanguage(string $code): void
    {
        static::$currentLanguage = static::normalizeCode($code);
    }

    public static function currentLanguage(): string
    {
        if (static::$currentLanguage) {
            return static::$currentLanguage;
        }

        return 'en';
    }

    public static function normalizeCode(string $code): string
    {
        $code = strtolower(trim(str_replace('_', '-', $code)));

        if ($code === '') {
            return 'en';
        }

        $aliases = [
            'en' => 'en',
            'en-gb' => 'en',
            'en-us' => 'en',
            'fr' => 'fr',
            'fr-fr' => 'fr',
            'es' => 'es',
            'es-es' => 'es',
            'it' => 'it',
            'it-it' => 'it',
            'zh' => 'zh',
            'zh-cn' => 'zh',
            'zh-tw' => 'zh-tw',
        ];

        if (isset($aliases[$code])) {
            return $aliases[$code];
        }

        if (str_starts_with($code, 'zh-tw')) {
            return 'zh-tw';
        }

        return explode('-', $code)[0];
    }

    public static function isDefaultLanguage(?string $code = null): bool
    {
        return static::normalizeCode($code ?? static::currentLanguage()) === 'en';
    }

    public static function prefixForLanguage(?string $code = null): string
    {
        $code = static::normalizeCode($code ?? static::currentLanguage());

        // All languages (including English) use a URL prefix: /en, /it, /fr, …
        return '/'.$code;
    }

    public static function localeTag(?string $code = null): string
    {
        $lang = static::getLanguage($code ?? static::currentLanguage());

        return $lang['locale'] ?? 'en_GB';
    }

    public static function htmlLang(?string $code = null): string
    {
        return str_replace('_', '-', static::localeTag($code));
    }

    /** @return array<int, array{slug:string,name:string,locale:string,flag_code:?string,rtl:bool,term_id:int}> */
    public static function getLanguages(): array
    {
        if (static::$languagesCache !== null) {
            return static::$languagesCache;
        }

        static::$languagesCache = static::defaultLanguages();

        if (! Schema::hasTable('wp_terms') || ! Schema::hasTable('wp_term_taxonomy')) {
            return static::$languagesCache;
        }

        try {
            $rows = DB::table('wp_terms')
                ->join('wp_term_taxonomy', 'wp_terms.term_id', '=', 'wp_term_taxonomy.term_id')
                ->where('wp_term_taxonomy.taxonomy', 'language')
                ->select('wp_terms.term_id', 'wp_terms.slug', 'wp_terms.name', 'wp_term_taxonomy.description')
                ->orderBy('wp_term_taxonomy.term_id')
                ->get();
        } catch (\Throwable) {
            return static::$languagesCache;
        }

        if ($rows->isEmpty()) {
            return static::$languagesCache;
        }

        static::$languagesCache = [];

        foreach ($rows as $row) {
            $meta = static::parseSerialized($row->description);
            static::$languagesCache[] = [
                'term_id' => (int) $row->term_id,
                'slug' => static::normalizeCode($row->slug),
                'name' => (string) $row->name,
                'locale' => (string) ($meta['locale'] ?? 'en_GB'),
                'flag_code' => $meta['flag_code'] ?? null,
                'rtl' => (bool) ($meta['rtl'] ?? false),
            ];
        }

        return static::$languagesCache;
    }

    /** @return array<int, array{slug:string,name:string,locale:string,flag_code:?string,rtl:bool,term_id:int}> */
    protected static function defaultLanguages(): array
    {
        return [
            [
                'term_id' => 0,
                'slug' => 'en',
                'name' => 'English',
                'locale' => 'en_GB',
                'flag_code' => 'gb',
                'rtl' => false,
            ],
        ];
    }

    public static function getLanguage(string $code): ?array
    {
        $code = static::normalizeCode($code);

        foreach (static::getLanguages() as $language) {
            if ($language['slug'] === $code) {
                return $language;
            }
        }

        return null;
    }

    /** @param array<int, int> $postIds */
    public static function warmCachesForPostIds(array $postIds): void
    {
        $postIds = array_values(array_unique(array_filter(array_map('intval', $postIds))));
        if ($postIds === []) {
            return;
        }

        $missingLanguages = array_values(array_filter(
            $postIds,
            static fn (int $id): bool => ! isset(static::$postLanguageCache[$id]),
        ));

        if ($missingLanguages !== []) {
            foreach ($missingLanguages as $postId) {
                static::$postLanguageCache[$postId] = 'en';
            }

            $languageRows = DB::table('wp_term_relationships')
                ->join('wp_term_taxonomy', 'wp_term_relationships.term_taxonomy_id', '=', 'wp_term_taxonomy.term_taxonomy_id')
                ->join('wp_terms', 'wp_term_taxonomy.term_id', '=', 'wp_terms.term_id')
                ->whereIn('wp_term_relationships.object_id', $missingLanguages)
                ->where('wp_term_taxonomy.taxonomy', 'language')
                ->select('wp_term_relationships.object_id', 'wp_terms.slug')
                ->get();

            foreach ($languageRows as $row) {
                static::$postLanguageCache[(int) $row->object_id] = static::normalizeCode((string) $row->slug);
            }
        }

        $missingGroups = array_values(array_filter(
            $postIds,
            static fn (int $id): bool => ! isset(static::$translationGroupCache[$id]),
        ));

        if ($missingGroups !== []) {
            foreach ($missingGroups as $postId) {
                static::$translationGroupCache[$postId] = [];
            }

            $groupRows = DB::table('wp_term_relationships')
                ->join('wp_term_taxonomy', 'wp_term_relationships.term_taxonomy_id', '=', 'wp_term_taxonomy.term_taxonomy_id')
                ->whereIn('wp_term_relationships.object_id', $missingGroups)
                ->where('wp_term_taxonomy.taxonomy', 'post_translations')
                ->select('wp_term_relationships.object_id', 'wp_term_taxonomy.description')
                ->get();

            foreach ($groupRows as $row) {
                $group = static::parseSerialized((string) $row->description);
                $normalized = [];

                if (is_array($group)) {
                    foreach ($group as $lang => $id) {
                        if (! is_string($lang) || ! is_numeric($id)) {
                            continue;
                        }
                        $normalized[static::normalizeCode($lang)] = (int) $id;
                    }
                }

                static::$translationGroupCache[(int) $row->object_id] = $normalized;
            }
        }
    }

    public static function getPostLanguage(int $postId): string
    {
        if (isset(static::$postLanguageCache[$postId])) {
            return static::$postLanguageCache[$postId];
        }

        $slug = DB::table('wp_term_relationships')
            ->join('wp_term_taxonomy', 'wp_term_relationships.term_taxonomy_id', '=', 'wp_term_taxonomy.term_taxonomy_id')
            ->join('wp_terms', 'wp_term_taxonomy.term_id', '=', 'wp_terms.term_id')
            ->where('wp_term_relationships.object_id', $postId)
            ->where('wp_term_taxonomy.taxonomy', 'language')
            ->value('wp_terms.slug');

        return static::$postLanguageCache[$postId] = static::normalizeCode((string) $slug ?: 'en');
    }

    public static function getTranslationGroup(int $postId): array
    {
        if (isset(static::$translationGroupCache[$postId])) {
            return static::$translationGroupCache[$postId];
        }

        $description = DB::table('wp_term_relationships')
            ->join('wp_term_taxonomy', 'wp_term_relationships.term_taxonomy_id', '=', 'wp_term_taxonomy.term_taxonomy_id')
            ->where('wp_term_relationships.object_id', $postId)
            ->where('wp_term_taxonomy.taxonomy', 'post_translations')
            ->value('wp_term_taxonomy.description');

        $group = static::parseSerialized((string) $description);
        $normalized = [];

        if (is_array($group)) {
            foreach ($group as $lang => $id) {
                if (! is_string($lang) || ! is_numeric($id)) {
                    continue;
                }
                $normalized[static::normalizeCode($lang)] = (int) $id;
            }
        }

        return static::$translationGroupCache[$postId] = $normalized;
    }

    public static function getTranslationId(int $postId, string $lang): int
    {
        $lang = static::normalizeCode($lang);
        $group = static::getTranslationGroup($postId);

        if (! empty($group[$lang])) {
            return (int) $group[$lang];
        }

        return static::getPostLanguage($postId) === $lang ? $postId : 0;
    }

    public static function getFrontPageId(): int
    {
        return (int) CalCache::remember(
            'cal.frontpage.id',
            now()->addDay(),
            static function (): int {
                if (! Schema::hasTable('wp_options')) {
                    return 21;
                }

                try {
                    return (int) (DB::table('wp_options')
                        ->where('option_name', 'page_on_front')
                        ->value('option_value') ?: 21);
                } catch (\Throwable) {
                    return 21;
                }
            },
        );
    }

    public static function getFrontPage(?string $lang = null): ?Post
    {
        $lang = static::normalizeCode($lang ?? static::currentLanguage());
        $targetId = CalCache::remember(
            "cal.frontpage.post_id.v2.{$lang}",
            now()->addHour(),
            static function () use ($lang): int {
                $frontId = static::getFrontPageId();
                if ($lang === 'en') {
                    return $frontId;
                }

                // No English fallback — each language must have its own front page.
                return static::getTranslationId($frontId, $lang) ?: 0;
            },
        );

        if (! $targetId) {
            return null;
        }

        return Post::with('meta')->published()->find($targetId);
    }

    public static function findPageBySlug(string $slug, ?string $lang = null): ?Post
    {
        $lang = static::normalizeCode($lang ?? static::currentLanguage());
        $slug = trim($slug, '/');
        $decoded = rawurldecode($slug);
        $encodedLower = strtolower(rawurlencode($decoded));
        $slugVariants = array_values(array_unique(array_filter([
            $slug,
            $decoded,
            $encodedLower,
            rawurlencode($decoded),
        ])));

        $candidates = Post::with('meta')
            ->whereIn('post_name', $slugVariants)
            ->where('post_type', 'page')
            ->published()
            ->get();

        foreach ($candidates as $candidate) {
            if (static::getPostLanguage($candidate->ID) === $lang) {
                return $candidate;
            }
        }

        foreach ($candidates as $candidate) {
            $translatedId = static::getTranslationId($candidate->ID, $lang);
            if ($translatedId) {
                return Post::with('meta')->published()->find($translatedId);
            }
        }

        // No English/content fallback — the page must exist in the requested language.
        return null;
    }

    public static function findCourseBySlug(string $slug, ?string $lang = null): ?Post
    {
        $lang = static::normalizeCode($lang ?? static::currentLanguage());
        $slug = cal_normalize_course_slug($slug);
        $decoded = rawurldecode($slug);
        $slugVariants = array_values(array_unique(array_filter([
            $slug,
            $decoded,
            rawurlencode($decoded),
            strtolower(rawurlencode($decoded)),
        ])));

        $candidates = Post::with('meta')
            ->whereIn('post_name', $slugVariants)
            ->where('post_type', 'ca_course')
            ->published()
            ->get();

        foreach ($candidates as $candidate) {
            if (static::getPostLanguage($candidate->ID) === $lang) {
                return $candidate;
            }
        }

        foreach ($candidates as $candidate) {
            $translatedId = static::getTranslationId($candidate->ID, $lang);
            if ($translatedId) {
                return Post::with('meta')->published()->find($translatedId);
            }
        }

        // No cross-language fallback — course must exist in the requested language.
        return null;
    }

    public static function buildPostUrl(int $postId, ?string $lang = null): string
    {
        $lang = static::normalizeCode($lang ?? static::currentLanguage());
        $post = Post::find($postId);

        if (! $post) {
            return static::prefixForLanguage($lang);
        }

        $prefix = static::prefixForLanguage($lang);
        $slug = $post->post_name;

        if ($post->post_type === 'page') {
            $frontId = static::getFrontPageId();
            $translatedFront = $lang === 'en'
                ? $frontId
                : (static::getTranslationId($frontId, $lang) ?: 0);

            if ((int) $postId === (int) $translatedFront || $slug === 'home') {
                return $prefix;
            }

            return $prefix.'/'.$slug;
        }

        if ($post->post_type === 'ca_course') {
            return $prefix.'/courses/'.$slug;
        }

        if ($post->post_type === 'post') {
            return $prefix.'/blog/'.$slug;
        }

        return $prefix.'/'.$slug;
    }

    /** @return array<int, array{slug:string,name:string,url:string,active:bool}> */
    public static function getLanguageSwitcherLinks(?int $postId = null): array
    {
        $postId = $postId ?: (int) request()->attributes->get('_post_id', 0);
        $cacheKey = $postId.':'.static::currentLanguage();

        if (isset(static::$languageSwitcherCache[$cacheKey])) {
            return static::$languageSwitcherCache[$cacheKey];
        }

        $links = [];

        foreach (static::getLanguages() as $language) {
            $code = $language['slug'];
            $targetId = $postId ? static::getTranslationId($postId, $code) : 0;

            if ($postId && ! $targetId && static::getPostLanguage($postId) === $code) {
                $targetId = $postId;
            }

            if ($postId && $targetId) {
                $url = static::buildPostUrl($targetId, $code);
            } else {
                // Language home when no translation of the current page exists.
                $url = static::prefixForLanguage($code);
            }

            $links[] = [
                'slug' => $code,
                'name' => $language['name'],
                'url' => $url,
                'flag_code' => $language['flag_code'],
                'active' => $code === static::currentLanguage(),
            ];
        }

        return static::$languageSwitcherCache[$cacheKey] = $links;
    }

    public static function localizedPath(string $path, ?string $lang = null): string
    {
        $lang = static::normalizeCode($lang ?? static::currentLanguage());
        $path = '/'.trim($path, '/');
        $prefix = static::prefixForLanguage($lang);

        if ($path === '/') {
            return $prefix;
        }

        // Already language-prefixed.
        if (preg_match('#^/(en|fr|es|it|zh|zh-tw)(/|$)#', $path)) {
            return $path === $prefix || str_starts_with($path, $prefix.'/')
                ? rtrim($path, '/') ?: $prefix
                : $path;
        }

        return $prefix.$path;
    }

    protected static function parseSerialized(string $value): array
    {
        if ($value === '') {
            return [];
        }

        $parsed = @unserialize($value, ['allowed_classes' => false]);

        return is_array($parsed) ? $parsed : [];
    }
}
