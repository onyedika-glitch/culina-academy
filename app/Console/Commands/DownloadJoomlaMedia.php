<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DownloadJoomlaMedia extends Command
{
    protected $signature = 'app:download-joomla-media
                            {--base-url=https://www.example.com}
                            {--output-dir= : Local directory to save files}';

    protected $description = 'Download all media files from Joomla and create attachment records';

    private string $cookieFile = '/tmp/joomla_cookies.txt';

    private string $baseUrl = 'https://www.example.com';

    private string $outputDir = '';

    private array $downloaded = [];

    private array $errors = [];

    public function handle(): int
    {
        $this->baseUrl = $this->option('base-url');
        $this->outputDir = $this->option('output-dir') ?: public_path('uploads/joomla');

        // Ensure login session is still valid
        $this->ensureLogin();

        // 1. Extract all image references from the extracted JSON articles
        $this->info('Phase 1: Extracting image references from articles...');
        $images = $this->extractImageReferences();
        $this->info('Found '.count($images).' unique image references');

        // 2. Download each image
        $this->info('Phase 2: Downloading media files...');
        $this->downloadAll($images);

        // 3. Create attachment records in the database
        $this->info('Phase 3: Creating attachment records...');
        $this->createAttachments();

        // 4. Update post_content to use local paths
        $this->info('Phase 4: Updating content references...');
        $this->updateContentReferences();

        $this->newLine();
        $this->table(
            ['Result', 'Count'],
            [
                ['Downloaded', count($this->downloaded)],
                ['Errors', count($this->errors)],
                ['Total unique', count($images)],
            ]
        );

        if (! empty($this->errors)) {
            $this->warn('Errors:');
            foreach ($this->errors as $err) {
                $this->line("  - {$err}");
            }
        }

        return self::SUCCESS;
    }

    private function extractImageReferences(): array
    {
        $images = [];
        $files = glob(storage_path('app/joomla-extract/articles/*.json'));

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (! $data) {
                continue;
            }

            $text = ($data['fulltext'] ?? '').($data['introtext'] ?? '').($data['image_intro'] ?? '').($data['image_fulltext'] ?? '');
            // Decode HTML entities
            $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Find all Joomla image paths
            preg_match_all('/src=["\']([^"\']*\.(?:jpg|jpeg|png|gif|webp|pdf|doc|docx))["\']/i', $text, $matches);
            foreach ($matches[1] as $src) {
                if (! str_starts_with($src, 'http')) {
                    $images[] = $src;
                }
            }

            // Also check direct image paths (like images/Restaurant_Partner/...)
            preg_match_all('/images\/[^"\'\s<>]+\.(?:jpg|jpeg|png|gif|webp|pdf)/i', $text, $directMatches);
            foreach ($directMatches[0] as $src) {
                if (! in_array($src, $images)) {
                    $images[] = $src;
                }
            }
        }

        // Also scan all existing DB posts for old Joomla image references
        $posts = DB::table('wp_posts')
            ->where('post_type', 'post')
            ->where('post_content', 'like', '%images/%')
            ->pluck('post_content');

        foreach ($posts as $content) {
            preg_match_all('/images\/[^"\'\s<>]+\.(?:jpg|jpeg|png|gif|webp|pdf)/i', $content, $m);
            foreach ($m[0] as $src) {
                if (! in_array($src, $images)) {
                    $images[] = $src;
                }
            }
        }

        $images = array_unique($images);
        sort($images);

        return $images;
    }

    private function downloadAll(array $images): void
    {
        $bar = $this->output->createProgressBar(count($images));
        $bar->start();

        foreach ($images as $path) {
            $cleanPath = ltrim(urldecode($path), '/');
            $localFile = $this->outputDir.'/'.$cleanPath;
            $localDir = dirname($localFile);

            if (! is_dir($localDir)) {
                mkdir($localDir, 0755, true);
            }

            // Skip if already downloaded
            if (file_exists($localFile) && filesize($localFile) > 0) {
                $this->downloaded[] = $path;
                $bar->advance();

                continue;
            }

            $url = $this->baseUrl.'/'.$cleanPath;
            $result = $this->downloadFile($url, $localFile);

            if ($result) {
                $this->downloaded[] = $path;
            } else {
                // Try alternative paths
                $altUrl = $this->baseUrl.'/'.$path;  // Already has images/ prefix
                $result = $this->downloadFile($altUrl, $localFile);
                if ($result) {
                    $this->downloaded[] = $path;
                } else {
                    $this->errors[] = "Failed: {$path}";
                }
            }

            $bar->advance();
            usleep(100000); // Rate limit
        }

        $bar->finish();
        $this->newLine();
    }

    private function downloadFile(string $url, string $localFile): bool
    {
        $ch = curl_init($url);
        $fp = fopen($localFile, 'w');
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64)',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FAILONERROR => true,
        ]);
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if ($result === false || $httpCode !== 200) {
            @unlink($localFile);

            return false;
        }

        return true;
    }

    private function createAttachments(): void
    {
        $count = 0;
        foreach ($this->downloaded as $path) {
            $cleanPath = ltrim($path, '/');
            $localFile = $this->outputDir.'/'.$cleanPath;

            if (! file_exists($localFile)) {
                continue;
            }

            $filename = basename($cleanPath);
            $mime = mime_content_type($localFile) ?: 'application/octet-stream';
            $size = filesize($localFile);
            $localUrl = '/uploads/joomla/'.$cleanPath;

            // Check if attachment already exists by guid
            $exists = DB::table('wp_posts')
                ->where('guid', $localUrl)
                ->where('post_type', 'attachment')
                ->exists();

            if ($exists) {
                continue;
            }

            // Create attachment post
            $slug = pathinfo($filename, PATHINFO_FILENAME);
            $slug = strtolower(preg_replace('/[^a-z0-9-]/', '-', $slug));
            $slug = trim(preg_replace('/-+/', '-', $slug), '-');

            DB::table('wp_posts')->insert([
                'post_author' => 1,
                'post_date' => now(),
                'post_date_gmt' => now()->utc(),
                'post_content' => '',
                'post_title' => $filename,
                'post_excerpt' => '',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_name' => $slug.'-'.uniqid(),
                'post_modified' => now(),
                'post_modified_gmt' => now()->utc(),
                'post_type' => 'attachment',
                'guid' => $localUrl,
                'post_mime_type' => $mime,
            ]);

            $attachId = DB::getPdo()->lastInsertId();

            // Store attachment metadata
            DB::table('wp_postmeta')->insert([
                'post_id' => $attachId,
                'meta_key' => '_wp_attached_file',
                'meta_value' => 'joomla/'.$cleanPath,
            ]);

            DB::table('wp_postmeta')->insert([
                'post_id' => $attachId,
                'meta_key' => '_wp_attachment_metadata',
                'meta_value' => serialize(['file' => 'joomla/'.$cleanPath, 'width' => 0, 'height' => 0]),
            ]);

            $count++;
        }

        $this->info("Created {$count} attachment records");
    }

    private function updateContentReferences(): void
    {
        $updated = 0;

        // Get all posts that might contain Joomla image references
        $posts = DB::table('wp_posts')
            ->where('post_type', 'post')
            ->where('post_content', 'like', '%images/%')
            ->get();

        foreach ($posts as $post) {
            $content = $post->post_content;
            $newContent = $content;

            // Replace Joomla image paths with local paths
            // images/... → /uploads/joomla/images/...
            $newContent = preg_replace(
                '#(src=["\'])(images/|/images/)#i',
                '$1/uploads/joomla/images/',
                $newContent
            );

            // Also fix inline references (not in src)
            $newContent = preg_replace(
                '#(["\'])(images/|/images/)#i',
                '$1/uploads/joomla/images/',
                $newContent
            );

            // Fix paths that already have double slashes
            $newContent = str_replace('//uploads', '/uploads', $newContent);
            // Fix paths starting with /joomla without /uploads
            $newContent = preg_replace('#(["\'])/joomla/#', '$1/uploads/joomla/', $newContent);

            if ($newContent !== $content) {
                DB::table('wp_posts')
                    ->where('ID', $post->ID)
                    ->update(['post_content' => $newContent]);
                $updated++;
            }
        }

        $this->info("Updated content references for {$updated} posts");
    }

    private function ensureLogin(): void
    {
        // Check if cookies exist and are still valid
        if (! file_exists($this->cookieFile) || filesize($this->cookieFile) < 50) {
            $this->login();
        }
    }

    private function login(): void
    {
        $this->info('Logging into Joomla admin...');
        $page = $this->fetch('https://www.example.com/administrator/index.php');
        $token = '';
        if (preg_match('/name="([a-f0-9]+)" value="1"/', $page, $m)) {
            $token = $m[1];
        }

        $this->fetch('https://www.example.com/administrator/index.php', [
            'username' => 'Naima',
            'passwd' => '123456',
            'option' => 'com_login',
            'task' => 'login',
            'return' => 'aW5kZXgucGhw',
            $token => '1',
        ]);
    }

    private function fetch(string $url, array $postData = []): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64)',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
        ]);

        if (! empty($postData)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        }

        $result = curl_exec($ch);
        curl_close($ch);

        return (string) $result;
    }
}
