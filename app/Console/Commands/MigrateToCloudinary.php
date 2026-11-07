<?php

namespace App\Console\Commands;

use App\Services\CloudinaryService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateToCloudinary extends Command
{
    protected $signature = 'app:migrate-to-cloudinary
                            {--path= : Relative path under public/uploads to migrate, e.g. "2026/05"}
                            {--dry-run : Show what would be migrated without uploading}';

    protected $description = 'Migrate existing public/uploads files to Cloudinary for CDN delivery';

    public function handle(): int
    {
        $basePath = public_path('uploads');
        $subPath = $this->option('path');
        $dryRun = $this->option('dry-run');

        $scanPath = $subPath ? "$basePath/$subPath" : $basePath;

        if (!is_dir($scanPath)) {
            $this->error("Directory not found: $scanPath");
            return self::FAILURE;
        }

        $files = $this->getFiles($scanPath);
        $count = count($files);

        if ($count === 0) {
            $this->info('No files found to migrate.');
            return self::SUCCESS;
        }

        $this->info("Found {$count} files to migrate from: {$scanPath}");
        $this->warn('Only images (jpg, jpeg, png, gif, webp) and videos (mp4, webm) will be uploaded to Cloudinary.');

        $uploaded = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($files as $file) {
            $relativePath = str_replace($basePath . '/', '', $file);
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm'];

            if (!in_array($ext, $allowedExts, true)) {
                $this->line("  ⏭ Skipped (unsupported): {$relativePath}");
                $skipped++;
                continue;
            }

            // Skip files that look like they're already Cloudinary-backed
            $cloudinaryFlagPath = $file . '.cloudinary';
            if (file_exists($cloudinaryFlagPath)) {
                $this->line("  ⏭ Already migrated: {$relativePath}");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("  🔄 Would upload: {$relativePath}");
                $uploaded++;
                continue;
            }

            // Create an UploadedFile-compatible upload
            $mime = mime_content_type($file) ?: 'application/octet-stream';
            $folder = 'culina-academy/migrated/' . dirname($relativePath);

            try {
                // Use curl-style upload since we don't have an UploadedFile instance
                $curlFile = new \CURLFile($file, $mime, basename($file));
                $cloudinary = new \Cloudinary\Cloudinary([
                    'cloud' => [
                        'cloud_name' => config('services.cloudinary.cloud_name'),
                        'api_key' => config('services.cloudinary.api_key'),
                        'api_secret' => config('services.cloudinary.api_secret'),
                    ],
                    'url' => ['secure' => true],
                ]);

                $resourceType = str_starts_with($mime, 'video/') ? 'video' : 'image';

                $result = $cloudinary->uploadApi()->upload($file, [
                    'folder' => $folder,
                    'resource_type' => $resourceType,
                    'overwrite' => false,
                    'access_mode' => 'public',
                ]);

                // Write flag file so we don't re-migrate
                file_put_contents($cloudinaryFlagPath, $result['secure_url']);

                $this->info("  ✅ Uploaded: {$relativePath} → {$result['secure_url']}");
                $uploaded++;
            } catch (\Throwable $e) {
                $this->error("  ❌ Failed: {$relativePath} — {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->table(
            ['Result', 'Count'],
            [
                ['Uploaded', $uploaded],
                ['Skipped', $skipped],
                ['Failed', $failed],
                ['Total', $count],
            ]
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function getFiles(string $dir): array
    {
        $result = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $result[] = $file->getRealPath();
            }
        }

        return $result;
    }
}
