<?php

namespace Tests\Support;

use App\Services\LegacyAcfSyncService;

/**
 * Test double that exposes LegacyAcfSyncService's protected parsing helpers.
 */
class ExposedLegacyAcfSyncService extends LegacyAcfSyncService
{
    public static function call(string $method, mixed ...$args): mixed
    {
        return static::{$method}(...$args);
    }
}
