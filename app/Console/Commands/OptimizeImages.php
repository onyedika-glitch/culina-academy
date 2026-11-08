<?php

namespace App\Console\Commands;

use App\Services\AcfFieldService;
use App\Services\ImageOptimizerService;
use App\Services\SiteCacheService;
use Illuminate\Console\Command;

class OptimizeImages extends Command
{
    protected $signature = 'cal:optimize-images
                            {--priority : Only optimize homepage hero, logo, and media-library attachments}
                            {--path= : Limit to a subdirectory inside public/uploads, e.g. 2026/05}
                            {--quality=82 : WebP quality from 1-100}
                            {--limit= : Maximum number of images to process}
                            {--force : Rebuild WebP files even when sidecars already exist}
                            {--dry-run : Show what would be optimized without writing files}';

    protected $description = 'Generate .webp sidecar files for images in public/uploads';

    public function handle(): int
    {
        $quality = max(1, min(100, (int) $this->option('quality')));
        $limit = $this->option('limit');
        $limit = $limit !== null && $limit !== '' ? max(1, (int) $limit) : null;

        try {
            $optimizer = new ImageOptimizerService(
                quality: $quality,
                force: (bool) $this->option('force'),
                dryRun: (bool) $this->option('dry-run'),
                limit: $limit,
                subdir: $this->option('path') ?: null,
                priorityOnly: (bool) $this->option('priority'),
            );
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $mode = $this->option('priority') ? 'priority images' : 'uploads directory';
        $this->info('Scanning '.$mode.' in '.$optimizer->uploadsRoot());

        if ($this->option('dry-run')) {
            $this->warn('Dry run only — no files will be written.');
        }

        $stats = $optimizer->run();

        $this->newLine();
        $this->line('Processed: '.$stats['processed']);
        $this->line('Skipped:   '.$stats['skipped']);
        $this->line('Failed:    '.$stats['failed']);
        $this->line('Saved:     '.$this->formatBytes($stats['saved_bytes']));

        if ($stats['errors'] !== []) {
            $this->newLine();
            $this->warn('Errors:');
            foreach (array_slice($stats['errors'], 0, 20) as $error) {
                $this->line(' - '.$error);
            }

            if (count($stats['errors']) > 20) {
                $this->line(' - ... and '.(count($stats['errors']) - 20).' more');
            }
        }

        if (! $this->option('dry-run') && $stats['processed'] > 0) {
            AcfFieldService::clearCache();
            SiteCacheService::clearAll();
            $this->info('Cleared site caches so WebP URLs are picked up immediately.');
            $this->line('Next: php artisan cal:warm-cache --url='.config('app.url'));
        }

        return $stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / (1024 * 1024), 2).' MB';
    }
}
