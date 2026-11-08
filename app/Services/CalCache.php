<?php

namespace App\Services;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Site cache that always uses the file store and never takes the site down
 * when CACHE_STORE=database but no cache table exists.
 */
class CalCache
{
    /** @var array<string, mixed> */
    protected static array $requestCache = [];

    public static function remember(string $key, int|\DateInterval|\DateTimeInterface $ttl, Closure $callback): mixed
    {
        if (array_key_exists($key, static::$requestCache)) {
            return static::$requestCache[$key];
        }

        try {
            $value = Cache::store('file')->remember($key, $ttl, $callback);
        } catch (Throwable) {
            $value = $callback();
        }

        return static::$requestCache[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, static::$requestCache)) {
            return static::$requestCache[$key];
        }

        try {
            $value = Cache::store('file')->get($key, $default);
        } catch (Throwable) {
            return $default;
        }

        return static::$requestCache[$key] = $value;
    }

    public static function put(string $key, mixed $value, int|\DateInterval|\DateTimeInterface $ttl): void
    {
        static::$requestCache[$key] = $value;

        try {
            Cache::store('file')->put($key, $value, $ttl);
        } catch (Throwable) {
            // Ignore cache backend errors.
        }
    }

    public static function forget(string $key): void
    {
        unset(static::$requestCache[$key]);

        try {
            Cache::store('file')->forget($key);
        } catch (Throwable) {
            // Ignore cache backend errors.
        }
    }

    public static function responseVersion(): int
    {
        return (int) static::get('cal.response.version', 1);
    }

    public static function bumpResponseVersion(): void
    {
        static::put('cal.response.version', static::responseVersion() + 1, now()->addYear());
        static::$requestCache = [];
    }

    public static function responseKey(Request $request): string
    {
        $path = rtrim($request->getPathInfo(), '/') ?: '/';
        $locale = (string) ($request->route('locale') ?? 'en');
        $version = static::responseVersion();

        return 'cal.response.v'.$version.'.'.sha1($path.'|'.$locale);
    }
}
