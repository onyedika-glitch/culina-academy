<?php

namespace App\Console\Commands;

use App\Services\CourseIntakeService;
use App\Services\SiteCacheService;
use Illuminate\Console\Command;

class RestoreAdvancedProfessionalChefDates extends Command
{
    protected $signature = 'cal:restore-advanced-professional-chef-dates';

    protected $description = 'Restore Advanced Professional Chef intake dates from example.com';

    public function handle(): int
    {
        $result = CourseIntakeService::restoreLegacyAdvancedProfessionalChefDates();

        SiteCacheService::clearResponseCache();

        $this->info("Updated {$result['updated']} course posts ({$result['skipped']} already correct).");
        $this->line('Course IDs: ' . implode(', ', $result['course_ids']));

        return self::SUCCESS;
    }
}