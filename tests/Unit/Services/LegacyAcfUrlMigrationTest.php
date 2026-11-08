<?php

namespace Tests\Unit\Services;

use Tests\Support\ExposedLegacyAcfSyncService as Sync;
use Tests\TestCase;

class LegacyAcfUrlMigrationTest extends TestCase
{
    public function test_migrate_meta_value_leaves_plain_text_and_email_addresses_alone(): void
    {
        $this->assertSame('', Sync::call('migrateMetaValue', ''));
        $this->assertSame('Learn to cook', Sync::call('migrateMetaValue', 'Learn to cook'));
        $this->assertSame('info@culinaacademy.com', Sync::call('migrateMetaValue', 'info@culinaacademy.com'));
    }

    public function test_migrate_meta_value_rewrites_legacy_image_paths(): void
    {
        $this->assertSame(
            '<img src="/uploads/joomla/images/kitchen.jpg">',
            Sync::call('migrateMetaValue', '<img src="/images/kitchen.jpg">'),
        );
    }

    public function test_migrate_meta_value_reserialises_nested_arrays_it_changes(): void
    {
        $value = serialize(['hero' => '<img src="/images/a.jpg">', 'title' => 'Welcome']);

        $migrated = unserialize(Sync::call('migrateMetaValue', $value));

        $this->assertSame('<img src="/uploads/joomla/images/a.jpg">', $migrated['hero']);
        $this->assertSame('Welcome', $migrated['title']);
    }

    public function test_migrate_meta_value_keeps_untouched_serialised_values_byte_identical(): void
    {
        $value = serialize(['title' => 'Welcome', 'count' => 3]);

        $this->assertSame($value, Sync::call('migrateMetaValue', $value));
    }

    public function test_migrate_meta_value_still_migrates_single_link_fields(): void
    {
        $this->assertSame('/uploads/joomla/images/logo.png', Sync::call('migrateMetaValue', '/images/logo.png'));
    }

    public function test_is_single_url_distinguishes_links_from_rich_text(): void
    {
        $this->assertTrue(Sync::call('isSingleUrl', '/en/about-us'));
        $this->assertTrue(Sync::call('isSingleUrl', 'https://example.com/courses?id=2'));
        $this->assertFalse(Sync::call('isSingleUrl', '<p>Hello</p>'));
        $this->assertFalse(Sync::call('isSingleUrl', 'Two words'));
        $this->assertFalse(Sync::call('isSingleUrl', ''));
    }
}
