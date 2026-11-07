<?php

namespace App\Services;

class AcfFieldTypeService
{
    public static function detect(string $key, mixed $value): string
    {
        $lower = strtolower($key);

        if (is_array($value) && (array_key_exists('url', $value) || array_key_exists('title', $value))) {
            return 'link';
        }

        if (static::looksLikeImageField($lower, $value)) {
            return 'image';
        }

        if (is_bool($value) || in_array($lower, ['cal_show_banner', 'cal_enable_form'], true)) {
            return 'boolean';
        }

        if (preg_match('/_(text|content|intro|paragraph|motivation|description|body|html)$/', $lower)
            || str_contains($lower, 'paragraph')
            || (is_string($value) && strlen($value) > 240)) {
            return 'textarea';
        }

        if (preg_match('/_(url|link_url|cta_url)$/', $lower)) {
            return 'url';
        }

        if (preg_match('/_(email)$/', $lower)) {
            return 'email';
        }

        return 'text';
    }

    public static function humanLabel(string $key): string
    {
        $label = preg_replace('/^(cal_|ca_)/', '', $key);
        $label = str_replace('_', ' ', $label);

        return ucwords($label);
    }

    public static function groupKey(string $key): string
    {
        if (preg_match('/^(cal_[a-z]+)_/', $key, $m)) {
            return ucwords(str_replace('_', ' ', str_replace('cal_', '', $m[1])));
        }

        return 'General';
    }

    protected static function looksLikeImageField(string $lower, mixed $value): bool
    {
        if (preg_match('/(image|logo|photo|bg|background|banner|thumbnail|icon|avatar)/', $lower)) {
            return true;
        }

        if (is_numeric($value) && (int) $value > 0) {
            $url = AcfFieldService::attachmentUrl((int) $value);

            return $url !== '';
        }

        if (is_array($value) && (isset($value['ID']) || isset($value['id']) || isset($value['url']))) {
            return true;
        }

        if (is_string($value) && preg_match('#^/uploads/|wp-content/uploads#', $value)) {
            return true;
        }

        return false;
    }

    public static function imagePreview(mixed $value): string
    {
        if (is_array($value)) {
            if (!empty($value['url'])) {
                return AcfFieldService::migrateUrl((string) $value['url']);
            }
            $id = (int) ($value['ID'] ?? $value['id'] ?? 0);

            return $id ? AcfFieldService::attachmentUrl($id) : '';
        }

        if (is_numeric($value)) {
            return AcfFieldService::attachmentUrl((int) $value);
        }

        if (is_string($value) && $value !== '') {
            return AcfFieldService::migrateUrl($value);
        }

        return '';
    }

    public static function imageStoredValue(mixed $value): string
    {
        if (is_numeric($value)) {
            return (string) (int) $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return $value;
        }

        return is_scalar($value) ? (string) $value : '';
    }
}