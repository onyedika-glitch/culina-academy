<?php

namespace App\Console\Commands;

use App\Exceptions\AcfSyncException;
use App\Models\Post;
use App\Services\CourseDisplayService;
use App\Services\JoomlaContentSyncService;
use App\Services\LegacyAcfSyncService;
use App\Services\LegacySiteSyncService;
use App\Services\SiteCacheService;
use Illuminate\Console\Command;

class SyncLegacySite extends Command
{
    protected $signature = 'cal:sync-legacy-site
                            {--courses-only : Only sync cooking course dates and metadata}
                            {--pages-only : Only sync static pages from the legacy public site (requires --force-joomla)}
                            {--acf-only : Only sync ACF template fields (requires --force-joomla)}
                            {--extract-only : Only sync from storage/app/joomla-extract JSON (requires --force-joomla)}
                            {--force-joomla : Allow destructive Joomla page/ACF sync (NOT recommended — use cal:restore-wordpress-content)}';

    protected $description = 'Sync course intake dates only. Page content comes from example.com via cal:restore-wordpress-content';

    public function handle(): int
    {
        $failed = 0;

        if ($this->option('courses-only')) {
            return $this->syncCourses();
        }

        if ($this->option('pages-only')) {
            return $this->guardJoomlaSync() ?? $this->syncPages();
        }

        if ($this->option('acf-only')) {
            return $this->guardJoomlaSync() ?? $this->syncAcf();
        }

        if ($this->option('extract-only')) {
            return $this->guardJoomlaSync() ?? $this->syncExtract();
        }

        $this->info('Default sync: course intake dates only (from live legacy site).');
        $this->warn('Page/ACF content lives on example.com — restore with: php artisan cal:restore-wordpress-content');
        $this->newLine();

        $failed += $this->syncCourses() !== self::SUCCESS ? 1 : 0;

        $descriptions = CourseDisplayService::fixCourseDescriptions();
        $this->info("Course descriptions checked: {$descriptions['updated']} repaired, {$descriptions['failed']} failed.");

        $meta = CourseDisplayService::fixEnglishCourseMeta();
        SiteCacheService::clearResponseCache();
        $this->info("English metadata fixed on {$meta['updated']} courses.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function syncCourses(): int
    {
        $this->info('Syncing cooking courses from '.LegacySiteSyncService::LEGACY_BASE_URL.' ...');

        $result = LegacySiteSyncService::syncAllLegacyCourses();
        foreach ($result['results'] as $row) {
            $course = Post::find($row['course_id']);
            $title = $course?->post_title ?? 'Course '.$row['course_id'];

            if (! empty($row['error'])) {
                $this->error("  ✗ {$title}: {$row['error']}");

                continue;
            }

            $this->line(($row['updated'] ? '  ✓ ' : '  – ')."{$title}: {$row['ranges']} intake(s)");
        }

        $this->info("Courses updated: {$result['updated']}, skipped: {$result['skipped']}, failed: {$result['failed']}");
        $this->newLine();

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function guardJoomlaSync(): ?int
    {
        if ($this->option('force-joomla')) {
            $this->warn('Running destructive Joomla sync — this can overwrite content from example.com.');

            return null;
        }

        $this->error('Joomla page/ACF sync is disabled. Content source is example.com.');
        $this->line('  Restore page content: php artisan cal:restore-wordpress-content');
        $this->line('  To force Joomla sync anyway: add --force-joomla (not recommended)');

        return self::FAILURE;
    }

    protected function syncPages(): int
    {
        $this->info('Syncing static pages from legacy public site (all languages) ...');

        $result = JoomlaContentSyncService::syncLegacyPublicPages();
        foreach ($result['results'] as $row) {
            $label = ($row['lang'] ?? 'en').'/'.$row['slug'];

            if (! empty($row['skipped'])) {
                continue;
            }

            if (! empty($row['error'])) {
                $this->error("  ✗ {$label}: {$row['error']}");

                continue;
            }

            $this->line(($row['updated'] ? '  ✓ ' : '  – ').$label);
        }

        $this->info("Pages updated: {$result['updated']}, skipped: {$result['skipped']}, failed: {$result['failed']}");
        $this->newLine();

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function syncAcf(): int
    {
        $this->info('Syncing ACF template fields from legacy public site (all languages) ...');

        try {
            $result = LegacyAcfSyncService::syncAllTemplateAcf();
        } catch (AcfSyncException $e) {
            $this->error("  ✗ Sync stopped: {$e->getMessage()}");
            $this->line('  See the application log for the full stack trace.');

            return self::FAILURE;
        }
        foreach ($result['results'] as $row) {
            $label = ($row['lang'] ?? 'en').'/'.$row['page'];

            if (! empty($row['skipped'])) {
                continue;
            }

            if (! empty($row['error'])) {
                $this->error("  ✗ {$label}: {$row['error']}");

                continue;
            }

            $this->line(($row['updated'] ? '  ✓ ' : '  – ').$label);
        }

        $this->info("ACF pages updated: {$result['updated']}, skipped: {$result['skipped']}, failed: {$result['failed']}, URL fixes: {$result['url_fixes']}");
        $this->newLine();

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function syncExtract(): int
    {
        $this->info('Syncing from Joomla extract JSON ...');

        $result = JoomlaContentSyncService::syncFromExtract();
        $this->info("Extract sync — updated: {$result['updated']}, imported: {$result['imported']}, skipped: {$result['skipped']}, failed: {$result['failed']}");
        $this->newLine();

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
