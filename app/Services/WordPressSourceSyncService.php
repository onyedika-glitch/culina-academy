<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostMeta;
use Illuminate\Support\Facades\DB;

/**
 * Restore content from the WordPress export at example.com.
 * This is the authoritative source — not the old Joomla site.
 */
class WordPressSourceSyncService
{
    public const SOURCE_BASE_URL = 'https://example.com';

    public static function defaultSqlPath(): string
    {
        return '/home/user/Downloads/audit-site/database.sql';
    }

    /** @return array{updated:int,skipped:int,failed:int,meta_rows:int} */
    public static function restoreFromSql(?string $sqlPath = null): array
    {
        $sqlPath = $sqlPath ?: static::defaultSqlPath();
        if (!is_readable($sqlPath)) {
            throw new \RuntimeException("WordPress SQL dump not found: {$sqlPath}");
        }

        if (str_contains($sqlPath, 'database.sql')) {
            $sqlPath = static::prepareFilteredMetaExtract($sqlPath);
        }

        $localPostIds = Post::query()->pluck('ID')->map(fn ($id) => (int) $id)->all();
        $localPostIdSet = array_fill_keys($localPostIds, true);

        $metaRows = static::parsePostMetaFromSql($sqlPath, $localPostIdSet);

        $postIds = array_keys($localPostIdSet);
        $touchedPosts = array_values(array_unique(array_map(
            static fn (array $row): int => (int) $row['post_id'],
            $metaRows,
        )));

        $updated = 0;
        $skipped = 0;
        $failed = 0;

        try {
            DB::transaction(function () use ($metaRows, $postIds, $touchedPosts, &$updated): void {
                DB::table('wp_postmeta')
                    ->whereIn('post_id', $postIds)
                    ->where(function ($q) {
                        $q->where('meta_key', 'like', 'cal%')
                            ->orWhere('meta_key', 'like', 'ca%')
                            ->orWhere('meta_key', 'like', 'rank_math%');
                    })
                    ->delete();

                foreach (array_chunk($metaRows, 250) as $chunk) {
                    $insert = array_map(static fn (array $row): array => [
                        'post_id' => (int) $row['post_id'],
                        'meta_key' => $row['meta_key'],
                        'meta_value' => $row['meta_value'],
                    ], $chunk);

                    DB::table('wp_postmeta')->insert($insert);
                    $updated += count($insert);
                }
            });
        } catch (\Throwable) {
            $failed = count($metaRows);
            $updated = 0;
        }

        foreach ($touchedPosts as $postId) {
            AcfFieldService::clearCache($postId);
        }

        SiteCacheService::clearResponseCache();

        return [
            'updated' => $updated,
            'skipped' => $skipped,
            'failed' => $failed,
            'meta_rows' => count($metaRows),
        ];
    }

    public static function isRestorableMetaKey(string $key): bool
    {
        if (str_starts_with($key, '_')) {
            return false;
        }

        return str_starts_with($key, 'cal_')
            || str_starts_with($key, 'ca_')
            || in_array($key, [
                'rank_math_title',
                'rank_math_description',
                'rank_math_focus_keyword',
                'rank_math_robots',
                'rank_math_canonical_url',
            ], true);
    }

    /**
     * @return array<int, array{post_id:int,meta_key:string,meta_value:string}>
     */
    /**
     * @param array<int, bool> $localPostIdSet
     * @return array<int, array{post_id:int,meta_key:string,meta_value:string}>
     */
    protected static function parsePostMetaFromSql(string $sqlPath, array $localPostIdSet): array
    {
        $rows = [];
        $handle = fopen($sqlPath, 'r');
        if ($handle === false) {
            return $rows;
        }

        while (($line = fgets($handle)) !== false) {
            if (!str_contains($line, 'INSERT INTO') || !str_contains($line, 'postmeta')) {
                continue;
            }

            if (!str_contains($line, 'cal_') && !str_contains($line, 'ca_') && !str_contains($line, 'rank_math_')) {
                continue;
            }

            if (!preg_match_all(
                '/\((\d+),\s*(\d+),\s*\'((?:\\\\\'|[^\'])*)\',\s*\'((?:\\\\\'|[^\'])*)\'\)/s',
                $line,
                $matches,
                PREG_SET_ORDER,
            )) {
                continue;
            }

            foreach ($matches as $match) {
                $postId = (int) $match[2];
                if (!isset($localPostIdSet[$postId])) {
                    continue;
                }

                $metaKey = static::unescapeSqlString($match[3]);
                if (!static::isRestorableMetaKey($metaKey)) {
                    continue;
                }

                $rows[] = [
                    'post_id' => $postId,
                    'meta_key' => $metaKey,
                    'meta_value' => static::unescapeSqlString($match[4]),
                ];
            }
        }

        fclose($handle);

        return $rows;
    }

    protected static function prepareFilteredMetaExtract(string $sqlPath): string
    {
        $cachePath = storage_path('app/wordpress-cal-postmeta.sql');
        if (is_readable($cachePath) && filemtime($cachePath) >= filemtime($sqlPath)) {
            return $cachePath;
        }

        $input = fopen($sqlPath, 'r');
        $output = fopen($cachePath, 'w');
        if ($input === false || $output === false) {
            return $sqlPath;
        }

        while (($line = fgets($input)) !== false) {
            if (!str_contains($line, 'postmeta')) {
                continue;
            }
            if (!str_contains($line, 'cal_') && !str_contains($line, 'ca_') && !str_contains($line, 'rank_math_')) {
                continue;
            }
            fwrite($output, $line);
        }

        fclose($input);
        fclose($output);

        return $cachePath;
    }

    protected static function unescapeSqlString(string $value): string
    {
        return str_replace(
            ["\\'", '\\"', '\\\\', '\\n', '\\r', '\\t'],
            ["'", '"', '\\', "\n", "\r", "\t"],
            $value,
        );
    }
}