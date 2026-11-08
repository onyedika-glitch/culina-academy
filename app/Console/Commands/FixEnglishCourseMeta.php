<?php

namespace App\Console\Commands;

use App\Services\CourseDisplayService;
use App\Services\SiteCacheService;
use Illuminate\Console\Command;

class FixEnglishCourseMeta extends Command
{
    protected $signature = 'cal:fix-english-course-meta';

    protected $description = 'Replace Spanish/French course card metadata with English on English ca_course posts';

    public function handle(): int
    {
        $result = CourseDisplayService::fixEnglishCourseMeta();

        SiteCacheService::clearResponseCache();

        $this->info("Updated {$result['updated']} English courses ({$result['skipped']} already correct).");

        return self::SUCCESS;
    }
}
