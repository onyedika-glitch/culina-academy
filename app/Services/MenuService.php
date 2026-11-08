<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\DB;

class MenuService
{
    public static function primaryMenuHtml(): string
    {
        $items = static::getPrimaryMenuItems();
        if (empty($items)) {
            return static::emptyMenuHtml();
        }

        return static::renderMenuTree($items);
    }

    public static function getPrimaryMenuItems(): array
    {
        $lang = PolylangService::currentLanguage();

        return CalCache::remember(
            "cal.menu.v4.items.{$lang}",
            now()->addHour(),
            static fn () => static::loadPrimaryMenuItems($lang),
        );
    }

    protected static function loadPrimaryMenuItems(string $lang): array
    {
        $lang = PolylangService::normalizeCode($lang);
        $menuSlug = static::menuSlugForLanguage($lang);

        $menuTerm = DB::table('wp_terms')
            ->join('wp_term_taxonomy', 'wp_terms.term_id', '=', 'wp_term_taxonomy.term_id')
            ->where('wp_term_taxonomy.taxonomy', 'nav_menu')
            ->where('wp_terms.slug', $menuSlug)
            ->select('wp_term_taxonomy.term_taxonomy_id')
            ->first();

        // Do not fall back to another language's menu — each locale must have its own.
        if (! $menuTerm) {
            return [];
        }

        $itemIds = DB::table('wp_term_relationships')
            ->where('term_taxonomy_id', $menuTerm->term_taxonomy_id)
            ->pluck('object_id');

        $posts = Post::with('meta')
            ->whereIn('ID', $itemIds)
            ->where('post_type', 'nav_menu_item')
            ->orderBy('menu_order')
            ->get();

        $flat = [];
        foreach ($posts as $item) {
            $resolved = static::resolveMenuItem($item, $lang);
            $flat[] = [
                'id' => $item->ID,
                'title' => $resolved['title'],
                'url' => $resolved['url'],
                'parent' => (int) ($item->getMeta('_menu_item_menu_item_parent') ?: 0),
                'order' => (int) $item->menu_order,
            ];
        }

        return static::buildTree($flat);
    }

    /**
     * Resolve title + URL for the active language only.
     * Course items always use that language's ca_course post — never another language's title.
     *
     * @return array{title: string, url: string}
     */
    protected static function resolveMenuItem(Post $item, string $lang): array
    {
        $type = (string) $item->getMeta('_menu_item_type', '');
        $objectId = (int) $item->getMeta('_menu_item_object_id', 0);
        $customUrl = (string) $item->getMeta('_menu_item_url', '');
        $storedTitle = trim((string) $item->post_title);

        // 1) Custom link that points at a course → language-specific course post.
        $course = static::courseFromCustomUrl($customUrl, $lang);
        if ($course) {
            return [
                'title' => $course->post_title,
                'url' => PolylangService::buildPostUrl((int) $course->ID, $lang),
            ];
        }

        // 2) Custom link that points at a site page → that language's page only.
        $page = static::pageFromCustomUrl($customUrl, $lang);
        if ($page) {
            return [
                'title' => $page->post_title,
                'url' => PolylangService::buildPostUrl((int) $page->ID, $lang),
            ];
        }

        // 3) WordPress object link (page / course / post) → only if a version exists in $lang.
        if (in_array($type, ['post_type', 'post_type_archive'], true) && $objectId > 0) {
            $targetId = static::postIdForLanguage($objectId, $lang);
            if ($targetId > 0) {
                $target = Post::query()->published()->find($targetId);
                if ($target) {
                    return [
                        'title' => $target->post_title !== '' ? $target->post_title : $storedTitle,
                        'url' => PolylangService::buildPostUrl((int) $target->ID, $lang),
                    ];
                }
            }
        }

        // 4) External / unknown custom links: keep stored title, localise path for this language.
        $url = $customUrl !== ''
            ? static::localiseCustomUrl($customUrl, $lang)
            : PolylangService::localizedPath('/', $lang);

        // Chrome labels only (Home, Cooking Courses, …) when we cannot resolve a post.
        $title = $storedTitle !== ''
            ? UiTranslationService::translateMenuTitle($storedTitle, $lang)
            : 'Link';

        return [
            'title' => $title,
            'url' => $url,
        ];
    }

    /**
     * Find the ca_course for $lang from a custom menu URL. Returns null if not a course link
     * or if that language has no published course for the slug (no cross-language fallback).
     */
    protected static function courseFromCustomUrl(string $url, string $lang): ?Post
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return null;
        }

        $path = '/'.trim($path, '/');

        // Strip locale prefix if present.
        if (preg_match('#^/(en|fr|es|it|zh|zh-tw)(/|$)#i', $path, $m)) {
            $path = substr($path, strlen($m[1]) + 1) ?: '/';
            $path = '/'.ltrim($path, '/');
        }

        // /course/{slug} or /courses/{slug}
        if (! preg_match('#^/courses?/([^/]+)/?$#i', $path, $m)) {
            return null;
        }

        $slug = cal_normalize_course_slug(rawurldecode($m[1]));

        return PolylangService::findCourseBySlug($slug, $lang);
    }

    /**
     * Find the page for $lang from a custom menu URL (single path segment).
     * Returns null if not a page link or that language has no published page (no fallback).
     */
    protected static function pageFromCustomUrl(string $url, string $lang): ?Post
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return null;
        }

        $path = '/'.trim($path, '/');

        if (preg_match('#^/(en|fr|es|it|zh|zh-tw)(/|$)#i', $path, $m)) {
            $path = substr($path, strlen($m[1]) + 1) ?: '/';
            $path = '/'.ltrim($path, '/');
        }

        // Single-segment site pages only (not /blog/x, /courses/x, home).
        if (! preg_match('#^/([a-z0-9\-]+)/?$#i', $path, $m)) {
            return null;
        }

        $slug = strtolower($m[1]);
        $reserved = [
            'blog', 'courses', 'course', 'admin', 'booking', 'api', 'uploads',
            'css', 'js', 'assets', 'contact-us', // contact-us has a dedicated route but still a page
        ];

        // Allow known page slugs including contact-us; block non-page roots only when not a page.
        if (in_array($slug, ['blog', 'courses', 'course', 'admin', 'booking', 'api', 'uploads', 'css', 'js', 'assets'], true)) {
            return null;
        }

        return PolylangService::findPageBySlug($slug, $lang);
    }

    /**
     * Return the post ID that exists in $lang for $sourceId, or 0 if none (no fallback).
     */
    protected static function postIdForLanguage(int $sourceId, string $lang): int
    {
        if ($sourceId <= 0) {
            return 0;
        }

        if (PolylangService::getPostLanguage($sourceId) === $lang) {
            return $sourceId;
        }

        return PolylangService::getTranslationId($sourceId, $lang) ?: 0;
    }

    protected static function localiseCustomUrl(string $url, string $lang): string
    {
        // External hosts that are not this site stay as-is (e.g. online course platform).
        $host = parse_url($url, PHP_URL_HOST);
        if (is_string($host) && $host !== '' && ! str_contains($host, 'example.com')) {
            return $url;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '' || $path === '/') {
            return PolylangService::localizedPath('/', $lang);
        }

        // Strip any existing locale, then re-prefix with the active language.
        $path = '/'.trim($path, '/');
        if (preg_match('#^/(en|fr|es|it|zh|zh-tw)(/|$)#i', $path, $m)) {
            $path = substr($path, strlen($m[1]) + 1) ?: '/';
            $path = '/'.ltrim($path, '/');
        }

        if (preg_match('#^/courses?/([^/]+)/?$#i', $path, $m)) {
            $path = '/courses/'.cal_normalize_course_slug(rawurldecode($m[1]));
        }

        return PolylangService::localizedPath($path === '/' ? '/' : $path, $lang);
    }

    protected static function buildTree(array $items, int $parentId = 0): array
    {
        $branch = [];
        foreach ($items as $item) {
            if ($item['parent'] !== $parentId) {
                continue;
            }
            $item['children'] = static::buildTree($items, $item['id']);
            $branch[] = $item;
        }

        return $branch;
    }

    /**
     * Match Cal_Crisp_Nav_Walker markup from the WordPress theme.
     */
    protected static function renderMenuTree(array $items): string
    {
        $currentPath = rtrim(parse_url(request()->url(), PHP_URL_PATH) ?: '/', '/') ?: '/';

        $html = '<ul id="menu-primary-menu" class="crisp-nav-list">';

        foreach ($items as $item) {
            $hasChildren = ! empty($item['children']);
            $itemPath = rtrim(parse_url($item['url'], PHP_URL_PATH) ?: '/', '/') ?: '/';
            $isActive = $itemPath === $currentPath;
            $itemClass = 'crisp-nav-item'.($isActive ? ' current-menu-item' : '');
            $linkClass = 'crisp-nav-link'.($isActive ? ' active' : '');

            $html .= '<li class="'.$itemClass.'">';
            $html .= '<a href="'.htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8').'" class="'.$linkClass.'"';
            if ($isActive) {
                $html .= ' aria-current="page"';
            }
            $html .= '>'.htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8');

            if ($hasChildren) {
                $html .= '<span class="crisp-nav-dropdown-icon"><svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor" aria-hidden="true"><path d="M7 10l5 5 5-5z"/></svg></span>';
            }

            $html .= '</a>';

            if ($hasChildren) {
                $html .= '<div class="crisp-nav-dropdown">';
                foreach ($item['children'] as $child) {
                    $childPath = rtrim(parse_url($child['url'], PHP_URL_PATH) ?: '/', '/') ?: '/';
                    $childActive = $childPath === $currentPath;
                    $childClass = 'crisp-nav-dropdown-item'.($childActive ? ' active current-menu-item' : '');
                    $html .= '<a href="'.htmlspecialchars($child['url'], ENT_QUOTES, 'UTF-8').'" class="'.$childClass.'"';
                    if ($childActive) {
                        $html .= ' aria-current="page"';
                    }
                    $html .= '>'.htmlspecialchars($child['title'], ENT_QUOTES, 'UTF-8').'</a>';
                }
                $html .= '</div>';
            }

            $html .= '</li>';
        }

        $html .= '</ul>';

        return $html;
    }

    protected static function menuSlugForLanguage(?string $lang = null): string
    {
        $lang = PolylangService::normalizeCode($lang ?? PolylangService::currentLanguage());

        return match ($lang) {
            'fr' => 'primary-menu-fr',
            'es' => 'primary-menu-es',
            'it' => 'primary-menu-it',
            'zh' => 'primary-menu-zh',
            'zh-tw' => 'primary-menu-zh-tw',
            default => 'primary-menu',
        };
    }

    protected static function emptyMenuHtml(): string
    {
        return '<ul id="menu-primary-menu" class="crisp-nav-list"></ul>';
    }
}
