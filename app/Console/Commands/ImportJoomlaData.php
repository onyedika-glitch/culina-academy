<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportJoomlaData extends Command
{
    protected $signature = 'app:import-joomla
                            {--source= : Directory with extracted Joomla JSON files}
                            {--dry-run : Show what would be imported without writing}';

    protected $description = 'Import ALL extracted Joomla content into the database (skips only exact slug duplicates)';

    public function handle(): int
    {
        $sourceDir = $this->option('source') ?: storage_path('app/joomla-extract');
        $dryRun = $this->option('dry-run');

        if (!is_dir("$sourceDir/articles")) {
            $this->error("Source directory not found: $sourceDir/articles");
            return self::FAILURE;
        }

        // Get ALL existing post slugs (lowercase) for dedup
        $existingSlugs = DB::table('wp_posts')
            ->pluck('post_name')
            ->map(fn ($s) => strtolower(trim($s)))
            ->toArray();

        // Get the current max ID so we can use proper auto-increment
        $maxId = DB::table('wp_posts')->max('ID') ?? 0;
        $maxMetaId = DB::table('wp_postmeta')->max('meta_id') ?? 0;

        $files = glob("$sourceDir/articles/*.json");
        $this->info('Found ' . count($files) . ' article files');

        $imported = 0;
        $skipped = 0;
        $nextId = $maxId + 1;

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (!$data || empty($data['title'])) {
                $skipped++;
                continue;
            }

            $title = trim(html_entity_decode($data['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $alias = !empty($data['alias']) ? strtolower(trim($data['alias'])) : Str::slug($title);

            // Skip language-association-only entries (those are not real articles)
            if (preg_match('/^(Category:|Uncategorised)/', $title) && strlen($title) < 60) {
                $skipped++;
                continue;
            }

            // Dedup by post_name (slug)
            if (in_array($alias, $existingSlugs)) {
                $skipped++;
                continue;
            }

            $content = $this->cleanContent($data['fulltext'] ?? $data['introtext'] ?? '');
            if (empty($content)) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("  Would import: {$title} ({$alias})");
                $imported++;
                continue;
            }

            $postId = $nextId++;

            DB::beginTransaction();
            try {
                // Insert the post with explicit ID to avoid sequence issues
                DB::statement('INSERT INTO wp_posts ("ID", post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_name, post_modified, post_modified_gmt, post_type, guid) VALUES (?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?)', [
                    $postId,
                    1,
                    $content,
                    $title,
                    '',
                    'publish',
                    'closed',
                    'closed',
                    $alias,
                    'post',
                    url('/?p=' . $postId),
                ]);

                // Track this slug so we don't import duplicates within the same batch
                $existingSlugs[] = $alias;

                // Import SEO metadata
                if (!empty($data['metadesc']) || !empty($data['metakey'])) {
                    if (!empty($data['metadesc'])) {
                        DB::statement('INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES (?, ?, ?)', [
                            $postId, 'rank_math_description', $data['metadesc']
                        ]);
                    }
                    if (!empty($data['metakey'])) {
                        DB::statement('INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES (?, ?, ?)', [
                            $postId, 'rank_math_focus_keyword', $data['metakey']
                        ]);
                    }
                    // Also store in seo_meta table
                    DB::statement('INSERT INTO seo_meta (seoable_type, seoable_id, title, description, focus_keyword, robots, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())', [
                        'App\Models\Post',
                        $postId,
                        $title,
                        $data['metadesc'] ?? '',
                        $data['metakey'] ?? '',
                        'index, follow',
                    ]);
                }

                DB::commit();
                $this->info("  ✅ #{$postId}: {$title}");
                $imported++;
            } catch (\Exception $e) {
                DB::rollBack();
                if (str_contains($e->getMessage(), 'duplicate key')) {
                    $this->warn("  ⏭ Skipped (duplicate): {$title}");
                    $skipped++;
                } else {
                    $this->error("  ❌ {$title}: {$e->getMessage()}");
                }
            }
        }

        // Fix the sequence for future auto-increment
        try {
            DB::statement("SELECT setval('wp_posts_id_seq', (SELECT MAX(id) FROM wp_posts))");
        } catch (\Throwable $e) {
            // sequence might not exist, that's ok
        }

        $this->newLine();
        $this->table(
            ['Result', 'Count'],
            [
                ['Imported', $imported],
                ['Skipped (exists/unrelated)', $skipped],
                ['Total files', count($files)],
            ]
        );

        return self::SUCCESS;
    }

    private function cleanContent(string $content): string
    {
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Remove Joomla shortcodes like {loadposition...} or [row][col...][/col][/row]
        $content = preg_replace('/\{[^}]+\}/', '', $content);
        $content = preg_replace('/\[row\]|\[\/row\]|\[col[^\]]*\]|\[\/col\]/', '', $content);
        // Remove Joomla editor comments
        $content = preg_replace('/<!--.*?-->/s', '', $content);

        return trim($content);
    }
}
