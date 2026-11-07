<?php

namespace App\Services;

class FlagService
{
    /** @var array<string, string> */
    protected static array $langToIso = [
        'en' => 'gb',
        'fr' => 'fr',
        'it' => 'it',
        'es' => 'es',
        'de' => 'de',
        'zh' => 'cn',
        'zh-tw' => 'tw',
        'pt' => 'pt',
        'nl' => 'nl',
        'ru' => 'ru',
        'ar' => 'sa',
        'pl' => 'pl',
        'ro' => 'ro',
    ];

    public static function isoCode(string $langCode): string
    {
        $langCode = PolylangService::normalizeCode($langCode);

        return static::$langToIso[$langCode] ?? $langCode;
    }

    public static function url(string $langCode): string
    {
        $iso = static::isoCode($langCode);
        $path = public_path("assets/images/flags/{$iso}.svg");

        if (is_file($path)) {
            return "/assets/images/flags/{$iso}.svg";
        }

        return 'https://flagcdn.com/w40/' . $iso . '.png';
    }
}