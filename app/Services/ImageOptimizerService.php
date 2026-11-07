<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

class ImageOptimizerService
{
    protected const SOURCE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];

    public function __construct(
        protected int $quality = 82,
        protected bool $force = false,
        protected bool $dryRun = false,
        protected ?int $limit = null,
        protected ?string $subdir = null,
        protected bool $priorityOnly = false,
    ) {}

    /** @return array{processed:int, skipped:int, failed:int, saved_bytes:int, errors:list<string>} */
    public function run(): array
    {
        $this->assertCanOptimize();

        $files = $this->priorityOnly
            ? $this->collectPriorityFiles()
            : $this->collectUploadFiles();

        $stats = [
            'processed' => 0,
            'skipped' => 0,
            'failed' => 0,
            'saved_bytes' => 0,
            'errors' => [],
        ];

        foreach ($files as $path) {
            if ($this->limit !== null && $stats['processed'] >= $this->limit) {
                break;
            }

            try {
                $result = $this->optimizeFile($path);
            } catch (\Throwable $e) {
                $stats['failed']++;
                $stats['errors'][] = $this->relativeUploadPath($path) . ': ' . $e->getMessage();
                continue;
            }

            if ($result === 'skipped') {
                $stats['skipped']++;
                continue;
            }

            $stats['processed']++;
            $stats['saved_bytes'] += $result;
        }

        return $stats;
    }

    public function uploadsRoot(): string
    {
        return public_path('uploads');
    }

    protected function assertCanOptimize(): void
    {
        if ($this->canUseGd() || $this->canUseImagick() || $this->canUseCwebp()) {
            return;
        }

        throw new RuntimeException(
            'No image encoder available. Enable PHP GD (with WebP support), Imagick, or install the cwebp binary.',
        );
    }

    /** @return list<string> */
    protected function collectUploadFiles(): array
    {
        $root = $this->uploadsRoot();
        if (!is_dir($root)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile() || !$this->isSourceImage($file->getPathname())) {
                continue;
            }

            if ($this->subdir !== null && !str_starts_with($this->relativeUploadPath($file->getPathname()), trim($this->subdir, '/'))) {
                continue;
            }

            $files[] = $file->getPathname();
        }

        sort($files);

        return $files;
    }

    /** @return list<string> */
    protected function collectPriorityFiles(): array
    {
        $paths = [];

        $attachedFiles = DB::table('wp_postmeta')
            ->where('meta_key', '_wp_attached_file')
            ->pluck('meta_value');

        foreach ($attachedFiles as $relative) {
            if (!is_string($relative) || $relative === '') {
                continue;
            }

            $absolute = $this->uploadsRoot() . '/' . ltrim($relative, '/');
            if (is_file($absolute) && $this->isSourceImage($absolute)) {
                $paths[$absolute] = $absolute;
            }
        }

        foreach ($this->collectHomeSlideFiles() as $absolute) {
            if (is_file($absolute) && $this->isSourceImage($absolute)) {
                $paths[$absolute] = $absolute;
            }
        }

        $logo = AcfFieldService::getOption('cal_header_logo');
        $logoUrl = AcfFieldService::imageUrl($logo);
        if ($logoUrl !== '' && str_starts_with($logoUrl, '/uploads/')) {
            $absolute = public_path(ltrim($logoUrl, '/'));
            if (is_file($absolute) && $this->isSourceImage($absolute)) {
                $paths[$absolute] = $absolute;
            }
        }

        $files = array_values($paths);
        sort($files);

        return $files;
    }

    /** @return list<string> */
    protected function collectHomeSlideFiles(): array
    {
        $paths = [];
        $frontPage = PolylangService::getFrontPage('en');
        if (!$frontPage) {
            return $paths;
        }

        AcfFieldService::hydratePostMeta($frontPage);
        $slides = AcfFieldService::getField($frontPage->ID, 'cal_home_slides', []);
        if (!is_array($slides)) {
            return $paths;
        }

        foreach ($slides as $slide) {
            if (!is_array($slide)) {
                continue;
            }

            $this->addImagePathsFromMeta($paths, $slide['image'] ?? null);

            $metadata = AcfFieldService::getAttachmentMetadata(
                AcfFieldService::attachmentIdFromImage($slide['image'] ?? null),
            );

            if (is_array($metadata)) {
                foreach (($metadata['sizes'] ?? []) as $size) {
                    if (!is_array($size) || empty($size['file'])) {
                        continue;
                    }

                    $baseDir = dirname((string) ($metadata['file'] ?? ''));
                    $file = (string) $size['file'];
                    $relative = str_contains($file, '/')
                        ? $file
                        : ($baseDir !== '.' && $baseDir !== '' ? $baseDir . '/' . $file : $file);

                    $absolute = $this->uploadsRoot() . '/' . ltrim($relative, '/');
                    if (is_file($absolute)) {
                        $paths[$absolute] = $absolute;
                    }
                }
            }
        }

        $enquiryBg = AcfFieldService::getField($frontPage->ID, 'cal_home_enquiry_bg');
        $this->addImagePathsFromMeta($paths, $enquiryBg);

        return array_values($paths);
    }

    /** @param array<string, string> $paths */
    protected function addImagePathsFromMeta(array &$paths, mixed $image): void
    {
        $src = AcfFieldService::attachmentSrc($image, 'full');
        if ($src === null || $src['url'] === '' || !str_starts_with($src['url'], '/uploads/')) {
            return;
        }

        $absolute = public_path(ltrim($src['url'], '/'));
        if (is_file($absolute)) {
            $paths[$absolute] = $absolute;
        }

        foreach (['large', 'medium_large', 'medium'] as $size) {
            $sized = AcfFieldService::attachmentSrc($image, $size);
            if ($sized === null || $sized['url'] === '' || !str_starts_with($sized['url'], '/uploads/')) {
                continue;
            }

            $sizedAbsolute = public_path(ltrim($sized['url'], '/'));
            if (is_file($sizedAbsolute)) {
                $paths[$sizedAbsolute] = $sizedAbsolute;
            }
        }
    }

    protected function optimizeFile(string $sourcePath): string|int
    {
        $webpPath = $sourcePath . '.webp';

        if (
            !$this->force
            && is_file($webpPath)
            && filemtime($webpPath) >= filemtime($sourcePath)
        ) {
            return 'skipped';
        }

        if ($this->dryRun) {
            return max(0, filesize($sourcePath));
        }

        $before = filesize($sourcePath) ?: 0;
        $this->encodeWebp($sourcePath, $webpPath);
        $after = is_file($webpPath) ? (filesize($webpPath) ?: 0) : 0;

        if ($after <= 0) {
            throw new RuntimeException('WebP output was not created.');
        }

        return max(0, $before - $after);
    }

    protected function encodeWebp(string $sourcePath, string $webpPath): void
    {
        if ($this->canUseGd()) {
            $this->encodeWithGd($sourcePath, $webpPath);

            return;
        }

        if ($this->canUseImagick()) {
            $this->encodeWithImagick($sourcePath, $webpPath);

            return;
        }

        $this->encodeWithCwebp($sourcePath, $webpPath);
    }

    protected function encodeWithGd(string $sourcePath, string $webpPath): void
    {
        $image = $this->loadGdImage($sourcePath);
        if ($image === false) {
            throw new RuntimeException('GD could not read the source image.');
        }

        if (!imagewebp($image, $webpPath, $this->quality)) {
            imagedestroy($image);
            throw new RuntimeException('GD failed to write WebP output.');
        }

        imagedestroy($image);
    }

    protected function loadGdImage(string $sourcePath): \GdImage|false
    {
        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => imagecreatefromjpeg($sourcePath),
            'png' => $this->loadPngWithAlpha($sourcePath),
            'gif' => $this->loadGifWithTruecolor($sourcePath),
            default => false,
        };
    }

    protected function loadPngWithAlpha(string $sourcePath): \GdImage|false
    {
        $image = imagecreatefrompng($sourcePath);
        if ($image === false) {
            return false;
        }

        $this->promoteToTruecolor($image, true);

        return $image;
    }

    protected function loadGifWithTruecolor(string $sourcePath): \GdImage|false
    {
        $image = imagecreatefromgif($sourcePath);
        if ($image === false) {
            return false;
        }

        $this->promoteToTruecolor($image, false);

        return $image;
    }

    protected function promoteToTruecolor(\GdImage $image, bool $preserveAlpha): void
    {
        if (function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($image);
        }

        if ($preserveAlpha) {
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }
    }

    protected function encodeWithImagick(string $sourcePath, string $webpPath): void
    {
        $image = new \Imagick($sourcePath);
        $image->setImageFormat('webp');
        $image->setImageCompressionQuality($this->quality);
        $image->writeImage($webpPath);
        $image->clear();
        $image->destroy();
    }

    protected function encodeWithCwebp(string $sourcePath, string $webpPath): void
    {
        $binary = $this->cwebpBinary();
        $command = sprintf(
            '%s -quiet -q %d %s -o %s',
            escapeshellarg($binary),
            $this->quality,
            escapeshellarg($sourcePath),
            escapeshellarg($webpPath),
        );

        exec($command, $output, $exitCode);
        if ($exitCode !== 0) {
            throw new RuntimeException('cwebp failed with exit code ' . $exitCode);
        }
    }

    protected function canUseGd(): bool
    {
        return function_exists('imagewebp')
            && (function_exists('imagecreatefromjpeg') || function_exists('imagecreatefrompng'));
    }

    protected function canUseImagick(): bool
    {
        return extension_loaded('imagick') && class_exists(\Imagick::class);
    }

    protected function canUseCwebp(): bool
    {
        return $this->cwebpBinary() !== null;
    }

    protected function cwebpBinary(): ?string
    {
        foreach (['cwebp', '/usr/bin/cwebp', '/usr/local/bin/cwebp'] as $candidate) {
            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        $path = trim((string) shell_exec('command -v cwebp 2>/dev/null'));
        if ($path !== '' && is_executable($path)) {
            return $path;
        }

        return null;
    }

    protected function isSourceImage(string $path): bool
    {
        if (str_ends_with(strtolower($path), '.webp')) {
            return false;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, self::SOURCE_EXTENSIONS, true);
    }

    protected function relativeUploadPath(string $absolutePath): string
    {
        $root = rtrim($this->uploadsRoot(), '/') . '/';

        return str_starts_with($absolutePath, $root)
            ? substr($absolutePath, strlen($root))
            : $absolutePath;
    }
}