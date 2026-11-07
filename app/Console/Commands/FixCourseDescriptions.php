<?php

namespace App\Console\Commands;

use App\Services\CourseDisplayService;
use Illuminate\Console\Command;

class FixCourseDescriptions extends Command
{
    protected $signature = 'cal:fix-course-descriptions';

    protected $description = 'Restore course page text from post_content or legacy site when ca_course_description only has metadata junk';

    public function handle(): int
    {
        $this->info('Repairing broken course descriptions ...');

        $result = CourseDisplayService::fixCourseDescriptions();

        foreach ($result['results'] as $row) {
            $label = ($row['lang'] ?? 'en') . '/' . ($row['slug'] ?? $row['course_id']);

            if (!empty($row['error'])) {
                $this->error("  ✗ {$label}: {$row['error']}");
                continue;
            }

            $this->line('  ✓ ' . $label . ' (' . ($row['source'] ?? 'fixed') . ')');
        }

        $this->info("Descriptions updated: {$result['updated']}, skipped: {$result['skipped']}, failed: {$result['failed']}");

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}