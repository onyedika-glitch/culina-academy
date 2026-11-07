<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ExtractJoomlaContent extends Command
{
    protected $signature = 'app:extract-joomla
                            {--output= : Output directory for extracted data}';

    protected $description = 'Extract all content from the Joomla site via admin API';

    private string $baseUrl = 'https://www.example.com/administrator';
    private string $cookieFile = '/tmp/joomla_cookies.txt';
    private string $token = '';

    public function handle(): int
    {
        $outputDir = $this->option('output') ?: storage_path('app/joomla-extract');
        @mkdir("$outputDir/articles", 0755, true);
        @mkdir("$outputDir/categories", 0755, true);
        @mkdir("$outputDir/menus", 0755, true);

        // Login
        if (!$this->login()) {
            $this->error('Failed to login to Joomla admin');
            return self::FAILURE;
        }

        // Extract categories
        $this->info('Extracting categories...');
        $this->extractCategories($outputDir);

        // Extract articles
        $this->info('Extracting articles list...');
        $articles = $this->extractArticleList();
        $this->info('Found ' . count($articles) . ' articles');

        // Extract each article's full content
        foreach ($articles as $id => $title) {
            $this->line("  Fetching article #{$id}: {$title}");
            $content = $this->fetchArticle($id);
            if ($content) {
                file_put_contents("$outputDir/articles/{$id}.json", json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }
            usleep(200000); // Rate limit
        }

        // Extract menus
        $this->info('Extracting menus...');
        $this->extractMenus($outputDir);

        $this->info("Extraction complete. Data saved to: {$outputDir}");
        return self::SUCCESS;
    }

    private function login(): bool
    {
        // Get login page with token
        $page = $this->fetch('index.php');
        if (preg_match('/name="([a-f0-9]+)" value="1"/', $page, $m)) {
            $this->token = $m[1];
        }

        $result = $this->fetch('index.php', [
            'username' => 'Naima',
            'passwd' => '123456',
            'option' => 'com_login',
            'task' => 'login',
            'return' => 'aW5kZXgucGhw',
            $this->token => '1',
        ]);

        return str_contains($result, 'Control Panel');
    }

    private function extractArticleList(): array
    {
        $html = $this->fetch('index.php?option=com_content&view=articles&limit=200');
        $articles = [];

        // Extract all article IDs and titles
        preg_match_all('/task=article\.edit[^"]*id=(\d+)/', $html, $idMatches);
        preg_match_all('/<a[^>]*href="[^"]*task=article\.edit[^"]*"[^>]*>([^<]+)<\/a>/', $html, $titleMatches);

        foreach ($idMatches[1] as $i => $id) {
            $title = $titleMatches[1][$i] ?? "Unknown #{$id}";
            // Skip language association links (they have title attributes, not actual titles)
            if (preg_match('/^(English|French|Spanish|Italian|Chinese)/', $title)) {
                continue;
            }
            $articles[$id] = html_entity_decode(trim($title));
        }

        return $articles;
    }

    private function fetchArticle(int $id): ?array
    {
        $html = $this->fetch("index.php?option=com_content&task=article.edit&id={$id}");

        if (empty($html) || str_contains($html, 'login')) {
            $this->warn("    Session expired, re-logging in...");
            $this->login();
            $html = $this->fetch("index.php?option=com_content&task=article.edit&id={$id}");
        }

        $data = [];

        // Title
        if (preg_match('/name="jform\[title\]"[^>]*value="([^"]*)"/', $html, $m)) {
            $data['title'] = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Alias (slug)
        if (preg_match('/name="jform\[alias\]"[^>]*value="([^"]*)"/', $html, $m)) {
            $data['alias'] = $m[1];
        }

        // Article text (Joomla editor uses different IDs)
        // Try Joomla editor textarea
        if (preg_match('/<textarea[^>]*name="jform\[articletext\][^"]*"[^>]*>(.*?)<\/textarea>/s', $html, $m)) {
            $data['fulltext'] = $m[1];
        } elseif (preg_match('/id="jform_articletext"[^>]*>(.*?)<\/textarea>/s', $html, $m)) {
            $data['fulltext'] = $m[1];
        }

        // Intro text (if using Joomla's split layout)
        if (preg_match('/id="jform_introtext"[^>]*>(.*?)<\/textarea>/s', $html, $m)) {
            $data['introtext'] = $m[1];
        }

        // Category
        if (preg_match('/name="jform\[catid\]"[^>]*selected="selected"[^>]*value="(\d+)"/', $html, $m)) {
            $data['catid'] = $m[1];
        } elseif (preg_match('/name="jform\[catid\]"[^>]*value="(\d+)"[^>]*selected/', $html, $m)) {
            $data['catid'] = $m[1];
        }

        // State (published/unpublished)
        if (preg_match('/name="jform\[state\]"[^>]*value="(\d+)"[^>]*(checked|selected)/', $html, $m)) {
            $data['state'] = (int) $m[1];
        }

        // Featured
        if (preg_match('/name="jform\[featured\]"[^>]*value="(\d+)"[^>]*(checked|selected)/', $html, $m)) {
            $data['featured'] = (int) $m[1];
        }

        // Language
        if (preg_match('/name="jform\[language\]"[^>]*selected="selected"[^>]*value="([^"]+)"/', $html, $m)) {
            $data['language'] = $m[1];
        }

        // Images
        if (preg_match('/name="jform\[images\]\[image_intro\]"[^>]*value="([^"]*)"/', $html, $m)) {
            $data['image_intro'] = $m[1];
        }
        if (preg_match('/name="jform\[images\]\[image_fulltext\]"[^>]*value="([^"]*)"/', $html, $m)) {
            $data['image_fulltext'] = $m[1];
        }

        // Metadata
        if (preg_match('/name="jform\[metadesc\]"[^>]*>(.*?)<\/textarea>/s', $html, $m)) {
            $data['metadesc'] = $m[1];
        }
        if (preg_match('/name="jform\[metakey\]"[^>]*>(.*?)<\/textarea>/s', $html, $m)) {
            $data['metakey'] = $m[1];
        }

        return $data;
    }

    private function extractCategories(string $outputDir): void
    {
        $html = $this->fetch('index.php?option=com_categories&extension=com_content&limit=200');
        preg_match_all('/data-details-id="(\d+)"/', $html, $ids);
        preg_match_all('/<a[^>]*href="[^"]*task=category\.edit[^"]*"[^>]*>([^<]+)<\/a>/', $html, $titles);

        $categories = [];
        foreach ($ids[1] as $i => $id) {
            $title = $titles[1][$i] ?? "Unknown";
            $categories[$id] = html_entity_decode(trim($title));
        }

        file_put_contents("$outputDir/categories.json", json_encode($categories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        foreach ($categories as $id => $title) {
            $this->line("  Cat #{$id}: {$title}");
        }
    }

    private function extractMenus(string $outputDir): void
    {
        // Get menu items
        $html = $this->fetch('index.php?option=com_menus&view=items&menutype=mainmenu&limit=200');
        preg_match_all('/data-details-id="(\d+)"/', $html, $ids);
        preg_match_all('/<a[^>]*href="[^"]*task=menuitem\.edit[^"]*"[^>]*>([^<]+)<\/a>/', $html, $titles);

        $menuItems = [];
        foreach ($ids[1] as $i => $id) {
            $title = $titles[1][$i] ?? "Unknown";
            $menuItems[$id] = html_entity_decode(trim($title));
        }

        file_put_contents("$outputDir/menus.json", json_encode($menuItems, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function fetch(string $path, array $postData = []): string
    {
        $url = $this->baseUrl . '/' . ltrim($path, '/');
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64)',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
        ]);

        if (!empty($postData)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        }

        $result = curl_exec($ch);
        curl_close($ch);

        return (string) $result;
    }
}
