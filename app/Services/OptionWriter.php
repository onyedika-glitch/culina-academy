<?php

namespace App\Services;

use App\Models\Option;

class OptionWriter
{
    public static function set(string $name, mixed $value): void
    {
        $key = str_starts_with($name, 'options_') ? $name : "options_{$name}";
        $stored = static::serializeValue($value);

        Option::updateOrCreate(
            ['option_name' => $key],
            ['option_value' => $stored, 'autoload' => 'yes'],
        );
    }

    /** @param array<int, array<string, mixed>> $rows */
    public static function setRepeater(string $fieldName, array $rows): void
    {
        $base = str_starts_with($fieldName, 'options_') ? $fieldName : "options_{$fieldName}";

        Option::where('option_name', 'like', $base . '_%')->delete();
        static::set($base, (string) count($rows));

        foreach ($rows as $index => $row) {
            if (!is_array($row)) {
                continue;
            }
            foreach ($row as $subKey => $subValue) {
                static::set("{$base}_{$index}_{$subKey}", $subValue);
            }
        }
    }

    /** @param array<string, mixed> $fields */
    public static function setFields(array $fields): void
    {
        foreach ($fields as $key => $value) {
            if (!is_string($key) || $key === '') {
                continue;
            }

            if (is_array($value) && static::isRepeaterPayload($value)) {
                static::setRepeater($key, $value);
                continue;
            }

            static::set($key, $value);
        }
    }

    protected static function isRepeaterPayload(array $value): bool
    {
        if ($value === []) {
            return true;
        }

        return array_is_list($value) && is_array($value[0] ?? null);
    }

    public static function hasRepeaterRows(string $fieldName): bool
    {
        $base = str_starts_with($fieldName, 'options_') ? $fieldName : "options_{$fieldName}";

        return Option::where('option_name', 'like', $base . '_0_%')->exists();
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