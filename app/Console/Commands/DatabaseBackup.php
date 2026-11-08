<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    protected $signature = 'app:backup
                            {--database= : PostgreSQL database name (default from .env)}
                            {--path= : Backup directory (default from env BACKUP_PATH)}';

    protected $description = 'Daily backup of the database and uploaded files';

    public function handle(): int
    {
        $dbName = $this->option('database') ?: env('DB_DATABASE', 'culina_academy');
        $dbUser = env('DB_USERNAME', 'chef_admin');
        $dbPass = env('DB_PASSWORD', '');
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '5432');

        $backupPath = $this->option('path') ?: env('BACKUP_PATH', '/home/user/backups');
        $retention = (int) env('BACKUP_RETENTION_DAYS', 30);

        $date = now()->format('Y-m-d-Hi');
        $dbDir = "$backupPath/db";
        $uploadsDir = "$backupPath/uploads";

        @mkdir($dbDir, 0755, true);
        @mkdir($uploadsDir, 0755, true);

        $dbFile = "$dbDir/{$dbName}-{$date}.sql.gz";
        $uploadsFile = "$uploadsDir/uploads-{$date}.tar.gz";

        $this->info("Backing up database: {$dbName}...");

        // Dump PostgreSQL database
        $dumpCmd = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s --clean --if-exists %s 2>/dev/null | gzip > %s',
            escapeshellarg($dbPass),
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbName),
            escapeshellarg($dbFile)
        );

        $exitCode = 0;
        $output = null;
        exec($dumpCmd, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error("Database backup failed (exit code: $exitCode)");

            return self::FAILURE;
        }

        $dbSize = filesize($dbFile);
        $this->info("Database backup saved: {$dbFile} (".round($dbSize / 1024 / 1024, 2).' MB)');

        // Backup uploads directory
        $publicPath = public_path('uploads');
        if (is_dir($publicPath)) {
            $this->info('Backing up uploads directory...');
            $tarCmd = sprintf(
                'tar -czf %s -C %s . 2>/dev/null',
                escapeshellarg($uploadsFile),
                escapeshellarg($publicPath)
            );
            exec($tarCmd, $output, $exitCode);

            if ($exitCode === 0) {
                $uploadsSize = filesize($uploadsFile);
                $this->info("Uploads backup saved: {$uploadsFile} (".round($uploadsSize / 1024 / 1024, 2).' MB)');
            } else {
                $this->warn('Uploads backup failed, continuing...');
            }
        }

        // Clean old backups beyond retention period
        $this->cleanOldBackups($dbDir, $retention, '*.sql.gz');
        $this->cleanOldBackups($uploadsDir, $retention, '*.tar.gz');

        $this->info('Backup completed successfully.');

        return self::SUCCESS;
    }

    private function cleanOldBackups(string $dir, int $days, string $pattern): void
    {
        $files = glob("$dir/$pattern");
        if (! $files) {
            return;
        }

        $cutoff = now()->subDays($days)->timestamp;
        $deleted = 0;

        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                @unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Cleaned {$deleted} old backup(s) from {$dir}");
        }
    }
}
