<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Keeps the legacy WordPress theme code out of the Laravel application.
 */
class ArchitectureTest extends TestCase
{
    private static function root(): string
    {
        return dirname(__DIR__, 2);
    }

    /** @return array<string, array{string}> */
    public static function applicationFiles(): array
    {
        $files = [];
        foreach (['app', 'routes', 'config', 'bootstrap'] as $dir) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::root().'/'.$dir));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php' && ! str_contains($file->getPathname(), '/bootstrap/cache/')) {
                    $relative = substr($file->getPathname(), strlen(self::root()) + 1);
                    $files[$relative] = [$relative];
                }
            }
        }

        return $files;
    }

    #[DataProvider('applicationFiles')]
    public function test_application_code_does_not_use_wordpress_hooks(string $file): void
    {
        $source = (string) file_get_contents(self::root().'/'.$file);

        $this->assertDoesNotMatchRegularExpression(
            '/(?<![\w>:$])(add_action|add_filter|remove_action|do_action|apply_filters)\s*\(/',
            $source,
            "{$file} calls a WordPress hook function; port the behaviour to Laravel instead.",
        );
    }

    #[DataProvider('applicationFiles')]
    public function test_application_code_does_not_load_the_legacy_theme(string $file): void
    {
        $source = (string) file_get_contents(self::root().'/'.$file);

        $this->assertStringNotContainsString('legacy/wordpress-theme', $source, "{$file} references the legacy theme folder.");
    }

    public function test_composer_does_not_autoload_the_legacy_theme(): void
    {
        $composer = json_decode((string) file_get_contents(self::root().'/composer.json'), true, flags: JSON_THROW_ON_ERROR);
        $autoload = json_encode($composer['autoload'] ?? [], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);

        $this->assertStringNotContainsString('legacy', $autoload);
        $this->assertStringNotContainsString('ThemeInc', $autoload);
    }

    public function test_the_legacy_folder_explains_itself(): void
    {
        $this->assertFileExists(self::root().'/legacy/wordpress-theme/README.md');
    }
}
