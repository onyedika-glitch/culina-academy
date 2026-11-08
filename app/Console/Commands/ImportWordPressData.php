<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportWordPressData extends Command
{
    protected $signature = 'app:import-wordpress
                            {--sql= : Path to the WordPress SQL dump file}
                            {--source= : Path to the extracted audit-site directory}';

    protected $description = 'Import WordPress content from SQL dump into Laravel/PostgreSQL';

    private array $prefixReplacements = [];

    public function handle(): int
    {
        $sqlPath = $this->option('sql') ?: '/home/user/Downloads/audit-site/database.sql';
        $sourceDir = $this->option('source') ?: '/home/user/Downloads/audit-site';

        if (! file_exists($sqlPath)) {
            $this->error("SQL file not found: $sqlPath");

            return self::FAILURE;
        }

        $this->info('Starting WordPress data import...');
        $this->newLine();

        // Phase 1: Parse and transform SQL
        $this->warn('Phase 1: Parsing SQL dump...');
        $sql = file_get_contents($sqlPath);
        // Replace SERVMASK_PREFIX with wp_
        $sql = str_replace('SERVMASK_PREFIX_', 'wp_', $sql);
        $sql = str_replace('`SERVMASK_PREFIX_', '`wp_', $sql);
        $sql = str_replace("'SERVMASK_PREFIX_", "'wp_", $sql);

        // Split into individual statements
        $statements = $this->splitSql($sql);
        $this->info('  Found '.count($statements).' SQL statements');

        // Phase 2: Import data (tables already created by migration)
        $this->warn('Phase 2: Importing data...');
        $insertCount = 0;
        $currentTable = '';
        foreach ($statements as $stmt) {
            $trimmed = trim($stmt);
            if (preg_match('/^INSERT\s+INTO\s+`?(wp_\w+)`?/i', $trimmed, $m)) {
                $currentTable = $m[1];
                $this->insertData($currentTable, $trimmed);
                $insertCount++;
                if ($insertCount % 50 == 0) {
                    $this->output->write('.');
                }
            }
        }
        $this->newLine();
        $this->info("  Processed $insertCount INSERT statements");

        // Phase 4: Copy media files
        $this->warn('Phase 4: Copying media uploads...');
        $uploadSrc = "$sourceDir/uploads";
        $uploadDst = public_path('uploads');
        if (is_dir($uploadSrc)) {
            $this->recurseCopy($uploadSrc, $uploadDst);
            $this->info('  Media files copied');
        }

        // Phase 5: Fix critical data
        $this->warn('Phase 5: Applying critical fixes...');
        $this->applyFixes();

        $this->newLine();
        $this->info('========================================');
        $this->info('IMPORT COMPLETE!');
        $this->info('========================================');
        $this->info('Admin URL: '.rtrim(config('app.url'), '/').'/admin');
        $this->info('Username: q (change after login)');
        $this->warn('IMPORTANT: Reset the admin password with:');
        $this->info('  php artisan app:reset-password');

        return self::SUCCESS;
    }

    private function splitSql(string $sql): array
    {
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];
            $next = $i + 1 < $len ? $sql[$i + 1] : '';

            if ($inString) {
                if ($ch === '\\' && $next === $stringChar) {
                    $current .= $ch.$next;
                    $i++;

                    continue;
                }
                if ($ch === $stringChar) {
                    $inString = false;
                }
                $current .= $ch;

                continue;
            }

            if ($ch === "'" || $ch === '"') {
                $inString = true;
                $stringChar = $ch;
                $current .= $ch;

                continue;
            }

            if ($ch === ';' && ($next === "\n" || $next === "\r" || $next === ' ' || $next === '')) {
                $trimmed = trim($current);
                if (! empty($trimmed)) {
                    $statements[] = $trimmed;
                }
                $current = '';

                continue;
            }

            $current .= $ch;
        }

        $trimmed = trim($current);
        if (! empty($trimmed)) {
            $statements[] = $trimmed;
        }

        return $statements;
    }

    private function createPgTable(string $createSql): void
    {
        // Extract table name
        if (! preg_match('/CREATE\s+TABLE\s+`?(wp_\w+)`?\s*\((.+)/is', $createSql, $m)) {
            return;
        }
        $tableName = $m[1];
        $defs = $m[2];

        // Remove closing paren, ENGINE, DEFAULT CHARSET, etc.
        $defs = preg_replace('/\)\s*(ENGINE|AUTO_INCREMENT|DEFAULT|COLLATE|ROW_FORMAT).*$/is', ')', $defs);

        // Clean up column definitions
        $defs = preg_replace('/\s+(ENGINE|AUTO_INCREMENT|DEFAULT|CHARSET|COLLATE)\s*=\s*\S+/i', '', $defs);
        $defs = preg_replace('/\)\s*[A-Z].*$/s', ')', trim($defs));

        // Convert MySQL types to PostgreSQL
        $conversions = [
            '/bigint\s*unsigned/i' => 'bigint',
            '/bigint/i' => 'bigint',
            '/int\s*unsigned/i' => 'integer',
            '/int\s*\(\d+\)/i' => 'integer',
            '/int/i' => 'integer',
            '/tinyint\s*unsigned/i' => 'smallint',
            '/tinyint\s*\(\d+\)/i' => 'smallint',
            '/tinyint/i' => 'smallint',
            '/smallint\s*unsigned/i' => 'smallint',
            '/smallint\s*\(\d+\)/i' => 'smallint',
            '/smallint/i' => 'smallint',
            '/varchar\s*\((\d+)\)/i' => 'varchar($1)',
            '/char\s*\((\d+)\)/i' => 'char($1)',
            '/longtext/i' => 'text',
            '/mediumtext/i' => 'text',
            '/tinytext/i' => 'text',
            '/longblob/i' => 'bytea',
            '/blob/i' => 'bytea',
            '/datetime/i' => 'timestamp',
            '/double/i' => 'double precision',
            '/float/i' => 'double precision',
            '/decimal\s*\((\d+),(\d+)\)/i' => 'numeric($1,$2)',
            '/text/i' => 'text',
        ];

        foreach ($conversions as $mysql => $pg) {
            $defs = preg_replace($mysql, $pg, $defs);
        }

        // Remove MySQL-specific clauses
        $defs = preg_replace('/CHARACTER\s+SET\s+\w+/i', '', $defs);
        $defs = preg_replace('/COLLATE\s+[\w_]+/i', '', $defs);
        $defs = preg_replace('/\s+USING\s+\w+/i', '', $defs);
        $defs = preg_replace('/\s+AUTO_INCREMENT/i', '', $defs);
        $defs = preg_replace('/\s+UNSIGNED/i', '', $defs);
        $defs = preg_replace('/,\s*\)/', ')', $defs);

        // Build clean CREATE TABLE
        $pgSql = "CREATE TABLE IF NOT EXISTS \"$tableName\" ($defs)";

        try {
            DB::statement($pgSql);
        } catch (\Exception $e) {
            $this->warn("  Warning creating $tableName: ".$e->getMessage());
        }
    }

    private function insertData(string $table, string $insertSql): void
    {
        // Extract column names or use table schema if not specified
        $columns = null;
        if (preg_match('/INSERT\s+INTO\s+`?(wp_\w+)`?\s*\(([^)]+)\)\s*VALUES/is', $insertSql, $m)) {
            $columns = array_map(function ($col) {
                return trim(str_replace('`', '', $col));
            }, explode(',', $m[2]));
        } else {
            // Get column names from database schema - include all columns
            try {
                $cols = DB::select("SELECT column_name FROM information_schema.columns 
                    WHERE table_name = ? AND table_schema = 'public' 
                    ORDER BY ordinal_position", [$table]);
                $columns = array_map(fn ($c) => $c->column_name, $cols);
            } catch (\Exception $e) {
                return;
            }
        }
        if (! $columns) {
            return;
        }

        // Extract all VALUES
        if (! preg_match('/VALUES\s+(.+)/is', $insertSql, $m)) {
            return;
        }

        $valuesPart = $m[1];
        // Split individual value groups
        $valueGroups = $this->splitValues($valuesPart);

        $batch = [];
        $batchSize = 200; // Optimal batch size for network latency

        foreach ($valueGroups as $valueGroup) {
            try {
                $values = $this->parseValues($valueGroup);

                if (count($columns) !== count($values)) {
                    continue;
                }

                $data = array_combine($columns, $values);
                $data = array_map(function ($v) {
                    if ($v === 'NULL' || $v === null) {
                        return null;
                    }
                    // Strip surrounding quotes
                    $v = trim($v);
                    if ((strlen($v) >= 2) && (
                        ($v[0] === "'" && $v[-1] === "'") ||
                        ($v[0] === '"' && $v[-1] === '"')
                    )) {
                        $v = substr($v, 1, -1);
                    }
                    // Unescape
                    $v = preg_replace("/\\\(['\"])/", '$1', $v);
                    $v = str_replace('\\n', "\n", $v);
                    $v = str_replace('\\r', "\r", $v);
                    $v = str_replace('\\t', "\t", $v);
                    $v = str_replace('\\\\', '\\', $v);

                    return $v;
                }, $data);

                $batch[] = $data;

                if (count($batch) >= $batchSize) {
                    $this->insertBatchWithFallback($table, $batch);
                    $batch = [];
                }
            } catch (\Exception $e) {
                // Skip problematic rows
                continue;
            }
        }

        if (! empty($batch)) {
            $this->insertBatchWithFallback($table, $batch);
        }
    }

    private function insertBatchWithFallback(string $table, array $batch): void
    {
        try {
            DB::table($table)->insert($batch);
        } catch (\Exception $e) {
            // Fallback to one-by-one inserts if batch fails (to skip duplicates)
            foreach ($batch as $row) {
                try {
                    DB::table($table)->insert($row);
                } catch (\Exception $ex) {
                    continue;
                }
            }
        }
    }

    private function splitValues(string $valuesPart): array
    {
        $groups = [];
        $current = '';
        $depth = 0;
        $inString = false;
        $stringChar = '';
        $len = strlen($valuesPart);

        for ($i = 0; $i < $len; $i++) {
            $ch = $valuesPart[$i];
            $next = $i + 1 < $len ? $valuesPart[$i + 1] : '';

            if ($inString) {
                if ($ch === '\\' && ($next === "'" || $next === '"' || $next === '\\')) {
                    $current .= $ch.$next;
                    $i++;

                    continue;
                }
                if ($ch === $stringChar) {
                    $inString = false;
                }
                $current .= $ch;

                continue;
            }

            if ($ch === "'" || $ch === '"') {
                $inString = true;
                $stringChar = $ch;
                $current .= $ch;

                continue;
            }

            if ($ch === '(') {
                if ($depth > 0) {
                    $current .= $ch;
                }
                $depth++;

                continue;
            }

            if ($ch === ')') {
                $depth--;
                if ($depth === 0) {
                    $groups[] = $current;
                    $current = '';

                    continue;
                }
                $current .= $ch;

                continue;
            }

            if ($depth > 0) {
                $current .= $ch;
            }
        }

        return $groups;
    }

    private function parseValues(string $valueStr): array
    {
        $values = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $len = strlen($valueStr);

        for ($i = 0; $i < $len; $i++) {
            $ch = $valueStr[$i];
            $next = $i + 1 < $len ? $valueStr[$i + 1] : '';

            if ($inString) {
                if ($ch === '\\' && ($next === "'" || $next === '"' || $next === '\\')) {
                    $current .= $ch.$next;
                    $i++;

                    continue;
                }
                if ($ch === $stringChar) {
                    $inString = false;
                }
                $current .= $ch;

                continue;
            }

            if ($ch === "'" || $ch === '"') {
                $inString = true;
                $stringChar = $ch;
                $current .= $ch;

                continue;
            }

            if ($ch === ',' && ! $inString) {
                $values[] = trim($current);
                $current = '';

                continue;
            }

            $current .= $ch;
        }

        if (trim($current) !== '') {
            $values[] = trim($current);
        }

        return $values;
    }

    private function applyFixes(): void
    {
        // Fix admin email
        DB::table('wp_options')
            ->where('option_name', 'admin_email')
            ->update(['option_value' => 'admin@example.com']);

        // Fix template/stylesheet (these don't apply to Laravel but we store for reference)
        DB::table('wp_options')
            ->where('option_name', 'template')
            ->update(['option_value' => 'generatepress']);
        DB::table('wp_options')
            ->where('option_name', 'stylesheet')
            ->update(['option_value' => 'generatepress']);

        // Set site URL
        DB::table('wp_options')
            ->where('option_name', 'siteurl')
            ->update(['option_value' => rtrim(config('app.url'), '/')]);
        DB::table('wp_options')
            ->where('option_name', 'home')
            ->update(['option_value' => rtrim(config('app.url'), '/')]);

        // Fix usermeta prefixes (e.g. SERVMASK_PREFIX_capabilities -> wp_capabilities)
        DB::table('wp_usermeta')
            ->where('meta_key', 'like', 'SERVMASK_PREFIX_%')
            ->update(['meta_key' => DB::raw("REPLACE(meta_key, 'SERVMASK_PREFIX_', 'wp_')")]);

        $this->info('  Critical fixes applied');
    }

    private function recurseCopy(string $src, string $dst): void
    {
        $dir = opendir($src);
        @mkdir($dst, 0755, true);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $srcPath = "$src/$file";
            $dstPath = "$dst/$file";
            if (is_dir($srcPath)) {
                $this->recurseCopy($srcPath, $dstPath);
            } else {
                copy($srcPath, $dstPath);
            }
        }
        closedir($dir);
    }
}
