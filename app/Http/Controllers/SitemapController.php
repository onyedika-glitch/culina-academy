<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\CalCache;
use App\Services\PolylangService;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SitemapController extends Controller
{
    public function robots(): Response
    {
        $base = rtrim((string) config('app.url'), '/');
        $body = implode("\n", [
            '# Generated from APP_URL — set APP_URL to your preferred public host',
            'User-agent: *',
            'Allow: /',
            '',
            'Sitemap: ' . $base . '/sitemap.xml',
            '',
        ]);

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function xml(): Response
    {
        $xml = CalCache::remember('cal.sitemap.xml.v2-locale-prefix', now()->addDay(), function (): string {
            return $this->buildXml();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    protected function buildXml(): string
    {
        $postsById = Post::query()
            ->whereIn('post_type', ['page', 'ca_course', 'post'])
            ->published()
            ->get(['ID', 'post_name', 'post_type', 'post_modified_gmt'])
            ->keyBy('ID');

        PolylangService::warmCachesForPostIds($postsById->keys()->all());

        $baseUrl = rtrim((string) config('app.url'), '/');
        $locales = ['en', 'fr', 'es', 'it', 'zh', 'zh-tw'];
        $frontId = PolylangService::getFrontPageId();

        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"',
            '        xmlns:xhtml="http://www.w3.org/1999/xhtml">',
        ];

        $lines = array_merge($lines, $this->homepageEntry($baseUrl, $locales, $frontId, $postsById));
        $lines = array_merge($lines, $this->entriesForType($postsById, $baseUrl, $locales, 'page', '0.8', 'monthly', $frontId));
        $lines = array_merge($lines, $this->entriesForType($postsById, $baseUrl, $locales, 'ca_course', '0.9', 'monthly'));
        $lines = array_merge($lines, $this->entriesForType($postsById, $baseUrl, $locales, 'post', '0.6', 'monthly'));

        $lines[] = '</urlset>';

        return implode("\n", $lines);
    }

    /** @param Collection<int, Post> $postsById */
    protected function homepageEntry(string $baseUrl, array $locales, int $frontId, Collection $postsById): array
    {
        $lines = [
            '  <url>',
            '    <loc>' . $this->escape($baseUrl . '/en') . '</loc>',
            '    <priority>1.0</priority>',
            '    <changefreq>weekly</changefreq>',
            '    <lastmod>' . date('Y-m-d') . '</lastmod>',
        ];

        foreach ($locales as $lang) {
            $href = $baseUrl . '/' . $lang;
            $lines[] = '    <xhtml:link rel="alternate" hreflang="' . $this->escape($lang) . '" href="' . $this->escape($href) . '"/>';
        }

        $lines[] = '    <xhtml:link rel="alternate" hreflang="x-default" href="' . $this->escape($baseUrl . '/en') . '"/>';
        $lines[] = '  </url>';

        return $lines;
    }

    /** @param Collection<int, Post> $postsById */
    protected function entriesForType(
        Collection $postsById,
        string $baseUrl,
        array $locales,
        string $postType,
        string $priority,
        string $changefreq,
        ?int $frontId = null,
    ): array {
        $lines = [];

        foreach ($postsById as $post) {
            if ($post->post_type !== $postType) {
                continue;
            }

            if ($frontId && (int) $post->ID === $frontId) {
                continue;
            }

            if ($post->post_name === 'home') {
                continue;
            }

            // Skip alias/duplicate pages that have 301 redirects instead
            $skipAliases = ['home-2', 'home-3', 'home-4', 'accueil', 'inicio'];
            if (in_array($post->post_name, $skipAliases, true)) {
                continue;
            }

            // Skip duplicate Chinese blog/careers routes — only publish the canonical (-zh / -zh-tw) version
            if (in_array($post->post_name, ['blog', 'careers'], true)) {
                continue;
            }

            $postLang = PolylangService::getPostLanguage((int) $post->ID);
            $pageUrl = $this->absolutePostUrl($post, $postLang, $baseUrl);
            $translationGroup = PolylangService::getTranslationGroup((int) $post->ID);

            $lines[] = '  <url>';
            $lines[] = '    <loc>' . $this->escape($pageUrl) . '</loc>';
            $lines[] = '    <priority>' . $this->escape($priority) . '</priority>';
            $lines[] = '    <changefreq>' . $this->escape($changefreq) . '</changefreq>';
            $lines[] = '    <lastmod>' . $this->escape(substr((string) ($post->post_modified_gmt ?? ''), 0, 10)) . '</lastmod>';

            foreach ($locales as $lang) {
                $altId = $translationGroup[$lang] ?? 0;
                if (!$altId || !$postsById->has($altId)) {
                    continue;
                }

                $altPost = $postsById->get($altId);
                $altUrl = $this->absolutePostUrl($altPost, $lang, $baseUrl);
                $lines[] = '    <xhtml:link rel="alternate" hreflang="' . $this->escape($lang) . '" href="' . $this->escape($altUrl) . '"/>';
            }

            if (!isset($translationGroup['en'])) {
                $lines[] = '    <xhtml:link rel="alternate" hreflang="x-default" href="' . $this->escape($pageUrl) . '"/>';
            }

            $lines[] = '  </url>';
        }

        return $lines;
    }

    protected function absolutePostUrl(Post $post, string $lang, string $baseUrl): string
    {
        return rtrim($baseUrl, '/') . $this->pathForPost($post, $lang);
    }

    protected function pathForPost(Post $post, string $lang): string
    {
        $lang = PolylangService::normalizeCode($lang);
        $prefix = PolylangService::prefixForLanguage($lang);
        $slug = $post->post_name;

        if ($post->post_type === 'ca_course') {
            return $prefix . '/courses/' . $slug;
        }

        if ($post->post_type === 'post') {
            return $prefix . '/blog/' . $slug;
        }

        return $prefix . '/' . $slug;
    }

    protected function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}