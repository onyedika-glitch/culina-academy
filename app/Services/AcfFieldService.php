<?php

namespace App\Services;

use App\Models\Option;
use App\Models\Post;
use App\Models\PostMeta;

class AcfFieldService
{
    protected static array $postMetaCache = [];
    protected static array $optionCache = [];
    protected static array $webpExistsCache = [];
    protected static array $attachmentMetadataCache = [];
    protected static array $attachmentFileCache = [];
    protected static array $urlToAttachmentIdCache = [];
    /** @var array<string, true>|null */
    protected static ?array $optionRepeaterBases = null;

    public static function hydratePostMeta(Post $post): void
    {
        $postId = (int) $post->ID;
        if ($postId <= 0 || isset(static::$postMetaCache["all.{$postId}"])) {
            return;
        }

        if (!$post->relationLoaded('meta')) {
            $post->load('meta');
        }

        $all = [];
        foreach ($post->meta as $meta) {
            if (str_starts_with((string) $meta->meta_key, '_')) {
                continue;
            }

            $all[$meta->meta_key] = $meta->meta_value;
            static::$postMetaCache["{$postId}.{$meta->meta_key}"] = $meta->meta_value;
        }

        static::$postMetaCache["all.{$postId}"] = $all;
    }

    public static function getField(int $postId, string $name, mixed $default = ''): mixed
    {
        if ($postId <= 0) {
            return $default;
        }

        $raw = static::getRawMeta($postId, $name);
        if ($raw === null || $raw === '') {
            $parentId = (int) static::getRawMeta($postId, '_atfpp_parent_post_id');
            if ($parentId > 0 && $parentId !== $postId) {
                return static::getField($parentId, $name, $default);
            }
            return $default;
        }

        if (static::isRepeaterCount($postId, $name, $raw)) {
            $repeater = static::getRepeater($postId, $name);
            if (empty($repeater)) {
                $parentId = (int) static::getRawMeta($postId, '_atfpp_parent_post_id');
                if ($parentId > 0 && $parentId !== $postId) {
                    return static::getRepeater($parentId, $name);
                }
            }
            return $repeater;
        }

        return static::parseValue($raw);
    }

    public static function getOption(string $name, mixed $default = ''): mixed
    {
        $lang = PolylangService::currentLanguage();
        $baseKey = str_starts_with($name, 'options_') ? $name : "options_{$name}";

        // For non-English locales, try a locale-specific option first.
        if ($lang !== 'en') {
            $localizedKey = $baseKey . '_' . $lang;
            $localizedValue = CalCache::remember(
                'cal.option.' . $localizedKey,
                now()->addHour(),
                static fn () => Option::where('option_name', $localizedKey)->value('option_value'),
            );
            if ($localizedValue !== null && $localizedValue !== '') {
                if (static::isOptionRepeaterCount($name, $localizedValue)) {
                    return static::getOptionRepeater($name . '_' . $lang);
                }
                return static::parseValue($localizedValue);
            }
        }

        // Fall back to the base (English) option.
        if (!isset(static::$optionCache[$baseKey])) {
            static::$optionCache[$baseKey] = CalCache::remember(
                'cal.option.' . $baseKey,
                now()->addHour(),
                static function () use ($baseKey) {
                    return Option::where('option_name', $baseKey)->value('option_value');
                },
            );
        }

        $raw = static::$optionCache[$baseKey];
        if ($raw === null || $raw === '') {
            return $default;
        }

        if (static::isOptionRepeaterCount($name, $raw)) {
            return static::getOptionRepeater($name);
        }

        return static::parseValue($raw);
    }

    public static function getRepeater(int $postId, string $fieldName): array
    {
        $prefix = $fieldName . '_';
        $meta = static::getAllMeta($postId);
        $items = [];

        foreach ($meta as $key => $value) {
            if (!preg_match('/^' . preg_quote($fieldName, '/') . '_(\d+)_(.+)$/', $key, $matches)) {
                continue;
            }
            $idx = (int) $matches[1];
            $sub = $matches[2];
            $items[$idx][$sub] = static::parseValue($value);
        }

        ksort($items);

        if (empty($items)) {
            $parentId = (int) static::getRawMeta($postId, '_atfpp_parent_post_id');
            if ($parentId > 0 && $parentId !== $postId) {
                return static::getRepeater($parentId, $fieldName);
            }
        } else {
            $parentId = (int) static::getRawMeta($postId, '_atfpp_parent_post_id');
            if ($parentId > 0 && $parentId !== $postId) {
                $parentItems = static::getRepeater($parentId, $fieldName);
                foreach ($items as $idx => &$item) {
                    if (isset($parentItems[$idx]) && is_array($parentItems[$idx])) {
                        $item = array_merge($parentItems[$idx], array_filter($item, fn($v) => $v !== null && $v !== ''));
                    }
                }
                unset($item);
            }
        }

        foreach ($items as $idx => $item) {
            $items[$idx] = static::normalizeNestedRepeaters($item);
        }

        return array_values($items);
    }

    public static function getOptionRepeater(string $fieldName): array
    {
        $base = str_starts_with($fieldName, 'options_') ? $fieldName : "options_{$fieldName}";

        return CalCache::remember(
            'cal.option.repeater.' . $base,
            now()->addHour(),
            static function () use ($fieldName, $base) {
                return static::loadOptionRepeater($fieldName, $base);
            },
        );
    }

    protected static function loadOptionRepeater(string $fieldName, string $base): array
    {
        $prefix = $base . '_';
        $options = Option::where('option_name', 'like', $prefix . '%')->get();
        $items = [];

        foreach ($options as $option) {
            if (!preg_match('/^' . preg_quote($base, '/') . '_(\d+)_(.+)$/', $option->option_name, $matches)) {
                continue;
            }
            $idx = (int) $matches[1];
            $sub = $matches[2];
            $items[$idx][$sub] = static::parseValue($option->option_value);
        }

        ksort($items);

        foreach ($items as $idx => $item) {
            $items[$idx] = static::normalizeNestedRepeaters($item);
        }

        return array_values($items);
    }

    /**
     * Rebuild nested ACF repeaters stored as flat keys (e.g. paragraphs_0_paragraph).
     */
    protected static function normalizeNestedRepeaters(array $item): array
    {
        $groups = [];
        $keysToRemove = [];

        foreach ($item as $key => $value) {
            if (!is_string($key) || !preg_match('/^([a-z][a-z0-9_]*)_(\d+)_([a-z][a-z0-9_]*)$/i', $key, $matches)) {
                continue;
            }

            $repeater = $matches[1];
            $row = (int) $matches[2];
            $sub = $matches[3];
            $groups[$repeater][$row][$sub] = $value;
            $keysToRemove[] = $key;
        }

        foreach ($groups as $repeater => $rows) {
            ksort($rows);

            $normalizedRows = [];
            foreach ($rows as $row) {
                $normalizedRows[] = is_array($row)
                    ? static::normalizeNestedRepeaters($row)
                    : $row;
            }

            $item[$repeater] = $normalizedRows;
        }

        foreach (array_unique($keysToRemove) as $key) {
            unset($item[$key]);
        }

        return $item;
    }

    public static function imageUrl(mixed $image): string
    {
        if (is_array($image) && !empty($image['url']) && is_string($image['url'])) {
            return static::webpWhenAvailable(static::migrateUrl($image['url']));
        }

        if (is_string($image) && $image !== '' && (str_starts_with($image, 'http') || str_starts_with($image, '/'))) {
            return static::webpWhenAvailable(static::migrateUrl($image));
        }

        $src = static::attachmentSrc($image, 'full');

        return $src['url'] ?? '';
    }

    /**
     * @return array{url:string,width:int,height:int}|null
     */
    public static function attachmentSrc(mixed $image, string $size = 'large'): ?array
    {
        if (is_array($image) && !empty($image['url']) && is_string($image['url'])) {
            $attachmentId = static::attachmentIdFromImage($image);
            if ($attachmentId <= 0) {
                $url = static::webpWhenAvailable(static::migrateUrl($image['url']));

                return [
                    'url' => $url,
                    'width' => (int) ($image['width'] ?? 0),
                    'height' => (int) ($image['height'] ?? 0),
                ];
            }
        }

        $attachmentId = static::attachmentIdFromImage($image);
        if ($attachmentId <= 0) {
            if (is_string($image) && $image !== '' && (str_starts_with($image, 'http') || str_starts_with($image, '/'))) {
                $url = static::webpWhenAvailable(static::migrateUrl($image));

                return ['url' => $url, 'width' => 0, 'height' => 0];
            }

            return null;
        }

        $metadata = static::attachmentMetadata($attachmentId);
        if ($metadata === null) {
            $url = static::attachmentUrl($attachmentId);

            return $url !== '' ? ['url' => $url, 'width' => 0, 'height' => 0] : null;
        }

        $baseDir = dirname((string) ($metadata['file'] ?? ''));
        $resolved = null;

        if ($size !== 'full' && !empty($metadata['sizes'][$size]) && is_array($metadata['sizes'][$size])) {
            $resolved = $metadata['sizes'][$size];
        }

        if ($resolved === null) {
            $resolved = [
                'file' => (string) ($metadata['file'] ?? ''),
                'width' => (int) ($metadata['width'] ?? 0),
                'height' => (int) ($metadata['height'] ?? 0),
            ];
        }

        $file = (string) ($resolved['file'] ?? '');
        if ($file === '') {
            return null;
        }

        $path = str_contains($file, '/')
            ? $file
            : ($baseDir !== '.' && $baseDir !== '' ? $baseDir . '/' . $file : $file);

        $url = static::webpWhenAvailable('/uploads/' . ltrim($path, '/'));

        return [
            'url' => $url,
            'width' => (int) ($resolved['width'] ?? 0),
            'height' => (int) ($resolved['height'] ?? 0),
        ];
    }

    public static function heroImageSrc(mixed $image): ?array
    {
        foreach (['large', 'medium_large', '1536x1536'] as $size) {
            $src = static::attachmentSrc($image, $size);
            if ($src !== null && $src['url'] !== '') {
                return $src;
            }
        }

        return static::attachmentSrc($image, 'full');
    }

    public static function attachmentSrcset(mixed $image, int $maxWidth = 1536): string
    {
        $attachmentId = static::attachmentIdFromImage($image);
        if ($attachmentId <= 0) {
            return '';
        }

        $metadata = static::attachmentMetadata($attachmentId);
        if ($metadata === null) {
            return '';
        }

        $baseDir = dirname((string) ($metadata['file'] ?? ''));
        $candidates = [];

        foreach (['medium', 'medium_large', 'large', '1536x1536'] as $sizeName) {
            if (empty($metadata['sizes'][$sizeName]) || !is_array($metadata['sizes'][$sizeName])) {
                continue;
            }

            $size = $metadata['sizes'][$sizeName];
            $file = (string) ($size['file'] ?? '');
            $width = (int) ($size['width'] ?? 0);

            if ($file === '' || $width <= 0) {
                continue;
            }

            $path = str_contains($file, '/')
                ? $file
                : ($baseDir !== '.' && $baseDir !== '' ? $baseDir . '/' . $file : $file);

            if ($width <= $maxWidth) {
                $candidates[$width] = static::webpWhenAvailable('/uploads/' . ltrim($path, '/'));
            }
        }

        if ($candidates === []) {
            return '';
        }

        ksort($candidates);
        $parts = [];
        foreach ($candidates as $width => $url) {
            $parts[] = $url . ' ' . $width . 'w';
        }

        return implode(', ', $parts);
    }

    public static function imageSizeAttrs(mixed $image, string $size = 'large'): string
    {
        $src = static::attachmentSrc($image, $size);
        if ($src === null || $src['width'] <= 0 || $src['height'] <= 0) {
            return '';
        }

        return ' width="' . $src['width'] . '" height="' . $src['height'] . '"';
    }

    public static function attachmentIdFromImage(mixed $image): int
    {
        if (is_array($image)) {
            if (!empty($image['ID'])) {
                return (int) $image['ID'];
            }
            if (!empty($image['id'])) {
                return (int) $image['id'];
            }
            if (!empty($image['url']) && is_string($image['url'])) {
                return static::attachmentIdFromUrl($image['url']);
            }
        }

        if (is_numeric($image)) {
            return (int) $image;
        }

        if (is_string($image) && $image !== '' && !str_starts_with($image, 'http') && !str_starts_with($image, '/')) {
            return (int) $image;
        }

        if (is_string($image) && $image !== '') {
            return static::attachmentIdFromUrl($image);
        }

        return 0;
    }

    protected static function attachmentIdFromUrl(string $url): int
    {
        $path = parse_url(static::migrateUrl($url), PHP_URL_PATH);
        if (!is_string($path) || !str_starts_with($path, '/uploads/')) {
            return 0;
        }

        $relative = ltrim(substr($path, strlen('/uploads/')), '/');
        if (str_ends_with($relative, '.webp')) {
            $relative = substr($relative, 0, -5);
        }

        if (array_key_exists($relative, static::$urlToAttachmentIdCache)) {
            return static::$urlToAttachmentIdCache[$relative];
        }

        $file = PostMeta::where('meta_key', '_wp_attached_file')
            ->where('meta_value', $relative)
            ->value('post_id');

        static::$urlToAttachmentIdCache[$relative] = (int) ($file ?: 0);

        return static::$urlToAttachmentIdCache[$relative];
    }

    /** @return array<string, mixed>|null */
    public static function getAttachmentMetadata(int $attachmentId): ?array
    {
        return static::attachmentMetadata($attachmentId);
    }

    /** @return array<string, mixed>|null */
    protected static function attachmentMetadata(int $attachmentId): ?array
    {
        if ($attachmentId <= 0) {
            return null;
        }

        if (array_key_exists($attachmentId, static::$attachmentMetadataCache)) {
            return static::$attachmentMetadataCache[$attachmentId];
        }

        $raw = PostMeta::where('post_id', $attachmentId)
            ->where('meta_key', '_wp_attachment_metadata')
            ->value('meta_value');

        if (!is_string($raw) || $raw === '') {
            static::$attachmentMetadataCache[$attachmentId] = null;

            return null;
        }

        $parsed = @unserialize($raw, ['allowed_classes' => false]);
        static::$attachmentMetadataCache[$attachmentId] = is_array($parsed) ? $parsed : null;

        return static::$attachmentMetadataCache[$attachmentId];
    }

    public static function attachmentUrl(int $attachmentId): string
    {
        if ($attachmentId <= 0) {
            return '';
        }

        if (!array_key_exists($attachmentId, static::$attachmentFileCache)) {
            static::$attachmentFileCache[$attachmentId] = PostMeta::where('post_id', $attachmentId)
                ->where('meta_key', '_wp_attached_file')
                ->value('meta_value');
        }

        $file = static::$attachmentFileCache[$attachmentId];

        if (!$file) {
            return '';
        }

        $url = '/uploads/' . ltrim($file, '/');

        // Serve WebP when available and browser supports it
        return static::webpWhenAvailable($url);
    }

    /**
     * Return a WebP URL if a .webp variant exists and the client accepts WebP.
     */
    public static function webpWhenAvailable(string $url): string
    {
        // Only convert local paths
        if (!str_starts_with($url, '/uploads/')) {
            return $url;
        }

        $webpPath = public_path(ltrim($url, '/') . '.webp');
        if (!array_key_exists($webpPath, static::$webpExistsCache)) {
            static::$webpExistsCache[$webpPath] = is_file($webpPath);
        }
        if (static::$webpExistsCache[$webpPath]) {
            return $url . '.webp';
        }

        return $url;
    }

    public static function migrateUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, '#') || preg_match('#^(?:mailto:|tel:|javascript:)#i', $url)) {
            return $url;
        }

        // Normalise scheme-less host URLs (e.g. culinaacademyonline.com/path)
        if (!str_contains($url, '://') && preg_match('#^([a-z0-9.-]+\\.[a-z]{2,})(/.*)?$#i', $url, $hostMatch)) {
            $url = 'https://' . $url;
        }

        $parsed = parse_url($url);
        $host = strtolower($parsed['host'] ?? '');

        // External online course platform has expired SSL — route to local detail pages
        if ($host && str_contains($host, 'culinaacademyonline.com')) {
            $detailPath = OnlineCourseService::courseSlugFromUrl($url);
            if ($detailPath) {
                return OnlineCourseService::detailPath($detailPath);
            }

            return OnlineCourseService::listingPath();
        }

        if ($host && !str_contains($host, 'example.com')) {
            return $url;
        }

        $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
        $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

        // Relative WordPress upload paths (no host)
        if (!$host && str_starts_with($url, '/wp-content/uploads/')) {
            return preg_replace('#^/wp-content/uploads/#', '/uploads/', $url) . $fragment;
        }

        $path = $parsed['path'] ?? '/';
        $path = '/' . trim($path, '/');
        $path = preg_replace('#^/wp-content/uploads/#', '/uploads/', $path);
        $path = preg_replace('#^/images/#', '/uploads/joomla/images/', $path);

        // Static assets must never receive a language prefix (/en/uploads would 404).
        if (preg_match('#^/(uploads|css|js|assets|fonts|favicon\.ico|robots\.txt|sitemap\.xml)(/|$)#i', $path)) {
            return rtrim($path, '/') . $query . $fragment;
        }

        // Strip .html extension from old Joomla URLs
        $path = preg_replace('/\\.html$/', '', $path);

        // Extract and remember locale prefix before other transformations.
        // All languages (including English) keep their URL prefix for page paths.
        $localePrefix = '';
        if (preg_match('#^/(en|fr|es|it|zh|zh-tw)(/|$)#', $path, $m)) {
            $localePrefix = $m[1];
            $path = substr($path, strlen('/' . $localePrefix)) ?: '/';
        }

        // Normalize path to start with /
        $path = '/' . ltrim($path, '/');

        // After stripping locale, static paths can surface again.
        if (preg_match('#^/(uploads|css|js|assets|fonts)(/|$)#i', $path)) {
            return rtrim($path, '/') . $query . $fragment;
        }

        // Convert old Joomla /course/ paths to /courses/
        if (preg_match('#^/course/([^/]+)/?$#', $path, $m)) {
            $path = '/courses/' . cal_normalize_course_slug($m[1]);
        }

        // Convert old Joomla online-courses/... to local detail pages
        // Use the current page language; ignore any hardcoded locale in the stored URL.
        $currentLang = PolylangService::normalizeCode(PolylangService::currentLanguage());
        $effectiveLocale = $currentLang;
        if (preg_match('#^/online-courses/introduction-to-the-professional-chef-online-course/?$#', $path)) {
            return OnlineCourseService::detailPath(
                OnlineCourseService::INTRODUCTION_SLUG,
                $effectiveLocale
            );
        }
        if (preg_match('#^/online-courses/michelin-star-advanced-techniques-online-course/?$#', $path)) {
            return OnlineCourseService::detailPath(
                OnlineCourseService::MICHELIN_SLUG,
                $effectiveLocale
            );
        }

        // Convert old Joomla cooking-courses/... to courses/...
        if (preg_match('#^/cooking-courses/([^/]+)/?$#', $path, $m)) {
            $slug = cal_normalize_course_slug($m[1]);
            // Strip locale prefix inside slug if it somehow ended up there
            $slug = ltrim(preg_replace('#^(en|fr|es|it|zh|zh-tw)/#', '', $slug), '/');
            $path = '/courses/' . $slug;
        } elseif (preg_match('#^/cooking-courses/?$#', $path)) {
            // Convert root cooking-courses listing (no specific course slug) to /courses
            $path = '/courses';
        }

        // Re-attach locale prefix for page/content paths only (default to current language).
        $currentLang = PolylangService::normalizeCode(PolylangService::currentLanguage());
        if ($localePrefix === '') {
            $localePrefix = $currentLang;
        } elseif ($localePrefix !== $currentLang) {
            // URL had a locale prefix that doesn't match the current page language.
            // Override so links respect the visitor's current locale (e.g. Italian home
            // page slides linking to English /en/cooking-courses → correct Italian /it/courses).
            $localePrefix = $currentLang;
        }
        if ($path === '/') {
            $path = '/' . $localePrefix;
        } elseif (!str_starts_with($path, '/' . $localePrefix . '/') && $path !== '/' . $localePrefix) {
            $path = '/' . $localePrefix . $path;
        }

        $path = rtrim($path, '/') ?: '/' . $localePrefix;

        return $path . $query . $fragment;
    }

    protected static function getRawMeta(int $postId, string $name): ?string
    {
        $cacheKey = "{$postId}.{$name}";
        if (!isset(static::$postMetaCache[$cacheKey])) {
            static::$postMetaCache[$cacheKey] = PostMeta::where('post_id', $postId)
                ->where('meta_key', $name)
                ->value('meta_value');
        }

        return static::$postMetaCache[$cacheKey];
    }

    protected static function getAllMeta(int $postId): array
    {
        if (!isset(static::$postMetaCache["all.{$postId}"])) {
            static::$postMetaCache["all.{$postId}"] = PostMeta::where('post_id', $postId)
                ->where('meta_key', 'not like', '\_%')
                ->pluck('meta_value', 'meta_key')
                ->all();
        }

        return static::$postMetaCache["all.{$postId}"];
    }

    protected static function isRepeaterCount(int $postId, string $name, string $raw): bool
    {
        if (!ctype_digit($raw)) {
            return false;
        }

        $prefix = $name . '_0_';

        if (isset(static::$postMetaCache["all.{$postId}"])) {
            foreach (array_keys(static::$postMetaCache["all.{$postId}"]) as $key) {
                if (str_starts_with($key, $prefix)) {
                    return true;
                }
            }

            return false;
        }

        return (bool) PostMeta::where('post_id', $postId)
            ->where('meta_key', 'like', $prefix . '%')
            ->exists();
    }

    protected static function isOptionRepeaterCount(string $name, string $raw): bool
    {
        if (!ctype_digit($raw)) {
            return false;
        }

        $base = str_starts_with($name, 'options_') ? $name : "options_{$name}";
        static::ensureOptionRepeaterBasesLoaded();

        return isset(static::$optionRepeaterBases[$base]);
    }

    protected static function ensureOptionRepeaterBasesLoaded(): void
    {
        if (static::$optionRepeaterBases !== null) {
            return;
        }

        static::$optionRepeaterBases = [];

        $rows = Option::query()
            ->where(function ($query): void {
                $query->where('option_name', 'like', '%\_0\_image')
                    ->orWhere('option_name', 'like', '%\_0\_link');
            })
            ->pluck('option_name');

        foreach ($rows as $optionName) {
            if (preg_match('/^(options_.+)_0_(?:image|link)$/', (string) $optionName, $matches)) {
                static::$optionRepeaterBases[$matches[1]] = true;
            }
        }
    }

    public static function parseValuePublic(mixed $value): mixed
    {
        return static::parseValue($value);
    }

    public static function clearCache(?int $postId = null): void
    {
        if ($postId) {
            foreach (array_keys(static::$postMetaCache) as $key) {
                if (str_starts_with($key, "{$postId}.") || $key === "all.{$postId}") {
                    unset(static::$postMetaCache[$key]);
                }
            }
            return;
        }

        static::$postMetaCache = [];
        static::$optionCache = [];
        static::$webpExistsCache = [];
        static::$attachmentMetadataCache = [];
        static::$attachmentFileCache = [];
        static::$urlToAttachmentIdCache = [];
        static::$optionRepeaterBases = null;
    }

    protected static function parseValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        while (is_string($value) && (str_starts_with($value, 'a:') || str_starts_with($value, 's:') || str_starts_with($value, 'i:') || str_starts_with($value, 'b:'))) {
            $unserialized = @unserialize($value);
            if ($unserialized === false && $unserialized !== serialize(false)) {
                break;
            }
            if ($unserialized === $value) {
                break;
            }
            $value = $unserialized;
        }

        if ($value === 'true') return true;
        if ($value === 'false') return false;

        return $value;
    }
}