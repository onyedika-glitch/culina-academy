<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// ── Daily database & uploads backup (runs at 3:00 AM) ──
Schedule::command('app:backup')
    ->dailyAt('03:00')
    ->when(fn () => env('BACKUP_ENABLED') === 'true')
    ->onSuccess(fn () => logger('Daily backup completed successfully.'))
    ->onFailure(fn () => logger('Daily backup FAILED.', ['error' => error_get_last()]));

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
