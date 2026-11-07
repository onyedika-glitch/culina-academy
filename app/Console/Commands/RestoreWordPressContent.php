<?php

namespace App\Console\Commands;

use App\Services\WordPressSourceSyncService;
use Illuminate\Console\Command;

class RestoreWordPressContent extends Command
{
    protected $signature = 'cal:restore-wordpress-content
                            {--sql= : Path to example.com database.sql export}';

    protected $description = 'Restore ACF fields and page content from the example.com WordPress export';

    public function handle(): int
    {
        $sqlPath = $this->option('sql') ?: WordPressSourceSyncService::defaultSqlPath();

        $this->info('Restoring content from WordPress export: ' . $sqlPath);
        $this->warn('Source: ' . WordPressSourceSyncService::SOURCE_BASE_URL);
        $this->newLine();

        try {
            $result = WordPressSourceSyncService::restoreFromSql($sqlPath);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Parsed {$result['meta_rows']} meta rows from export.");
        $this->info("Restored: {$result['updated']} updated, {$result['skipped']} skipped, {$result['failed']} failed.");

        $this->newLine();
        $this->info('Repairing course descriptions ...');
        $descriptions = \App\Services\CourseDisplayService::fixCourseDescriptions();
        $this->info("Course descriptions: {$descriptions['updated']} repaired.");

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}