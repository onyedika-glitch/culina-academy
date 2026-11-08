<?php

namespace Tests\Support;

use App\Models\Post;
use App\Services\LegacyAcfSyncService;
use Throwable;

/**
 * LegacyAcfSyncService whose per-template work throws or returns on demand.
 */
class ThrowingSync extends LegacyAcfSyncService
{
    public static ?Throwable $throw = null;

    public static bool $result = false;

    protected static function runTemplateSync(string $key, Post $page, string $lang, ?string $legacyPath): bool
    {
        if (static::$throw) {
            throw static::$throw;
        }

        return static::$result;
    }
}
