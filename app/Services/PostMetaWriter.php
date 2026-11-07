<?php

namespace App\Services;

use App\Models\PostMeta;
use Illuminate\Support\Facades\DB;

class PostMetaWriter
{
    public static function set(int $postId, string $key, mixed $value): void
    {
        $stored = static::serializeValue($value);

        PostMeta::updateOrCreate(
            ['post_id' => $postId, 'meta_key' => $key],
            ['meta_value' => $stored],
        );

        AcfFieldService::clearCache($postId);
        SiteCacheService::clearResponseCache();
        SiteCacheService::clearCarousel();
    }

    public static function delete(int $postId, string $key): void
    {
        PostMeta::where('post_id', $postId)->where('meta_key', $key)->delete();
        AcfFieldService::clearCache($postId);
        SiteCacheService::clearResponseCache();
        SiteCacheService::clearCarousel();
    }

    public static function deleteByPrefix(int $postId, string $prefix): void
    {
        PostMeta::where('post_id', $postId)
            ->where('meta_key', 'like', $prefix . '%')
            ->delete();

        AcfFieldService::clearCache($postId);
        SiteCacheService::clearResponseCache();
        SiteCacheService::clearCarousel();
    }

    /** @param array<int, array<string, mixed>> $rows */
    public static function setRepeater(int $postId, string $fieldName, array $rows): void
    {
        static::deleteByPrefix($postId, $fieldName . '_');

        static::set($postId, $fieldName, (string) count($rows));

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }
            foreach ($row as $subKey => $subValue) {
                if ($subKey === '' || is_array($subValue) && static::isAssoc($subValue)) {
                    static::set($postId, "{$fieldName}_{$index}_{$subKey}", $subValue);
                } else {
                    static::set($postId, "{$fieldName}_{$index}_{$subKey}", $subValue);
                }
            }
        }
    }

    /**
     * Write a repeater that may contain nested repeaters (e.g. FAQ categories → questions).
     *
     * @param array<int, array<string, mixed>> $rows
     */
    public static function setNestedRepeater(int $postId, string $fieldName, array $rows): void
    {
        static::deleteByPrefix($postId, $fieldName . '_');
        static::set($postId, $fieldName, (string) count($rows));

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }

            foreach ($row as $subKey => $subValue) {
                if ($subKey === '') {
                    continue;
                }

                if (is_array($subValue) && static::isRepeaterPayload($subValue)) {
                    static::setNestedRepeater($postId, "{$fieldName}_{$index}_{$subKey}", $subValue);
                    continue;
                }

                static::set($postId, "{$fieldName}_{$index}_{$subKey}", $subValue);
            }
        }
    }

    /** @param array<string, mixed> $fields */
    public static function setFields(int $postId, array $fields): void
    {
        foreach ($fields as $key => $value) {
            if (!is_string($key) || $key === '' || str_starts_with($key, '_')) {
                continue;
            }

            if (is_array($value) && static::isRepeaterPayload($value)) {
                static::setRepeater($postId, $key, $value);
                continue;
            }

            static::set($postId, $key, $value);
        }
    }

    protected static function isRepeaterPayload(array $value): bool
    {
        if ($value === []) {
            return true;
        }

        return array_is_list($value) && is_array($value[0] ?? null);
    }

    protected static function isAssoc(array $arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    protected static function serializeValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            return serialize($value);
        }

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }
}