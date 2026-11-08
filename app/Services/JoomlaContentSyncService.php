<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostMeta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class JoomlaContentSyncService
{
    /** @var array<string, string> Joomla article alias => new English page slug */
    public const PAGE_ALIAS_MAP = [
        'about-us' => 'about-us',
        'careers' => 'careers',
        'apply-now' => 'how-to-apply',
        'frequently-asked-questions' => 'faqs',
        'our-schools' => 'hospitality-development',
        'our-school' => 'hospitality-development',
        'asic-international-students' => 'international-students',
        'contact-us' => 'contact-us',
        'cooking-courses' => 'cooking-courses',
        'online-courses' => 'online-courses',
        'restaurant-partners' => 'restaurant-partners',
        'reviews' => 'reviews',
        'blog' => 'blog',
        'privacy-policy' => 'privacy-policy',
        'terms-conditions' => 'terms-and-conditions',
        'cookies-policy' => 'cookies-policy',
        'complaints-policy-and-procedure' => 'complaints-policy-and-procedure',
        'attendance-and-punctuality-policy' => 'attendance-and-punctuality-policy',
        'gdpr' => 'privacy-policy',
        'course-enrollment' => 'course-enrollment',
    ];

    /** @var array<string, string> Legacy public path (no .html) => page slug */
    public const LEGACY_PAGE_PATHS = [
        '' => 'home',
        'about-us' => 'about-us',
        'about-us/careers' => 'careers',
        'about-us/apply-now' => 'how-to-apply',
        'about-us/frequently-asked-questions' => 'faqs',
        'about-us/our-schools' => 'hospitality-development',
        'asic-international-students' => 'international-students',
        'contact-us' => 'contact-us',
        'cooking-courses' => 'cooking-courses',
        'restaurant-partners' => 'restaurant-partners',
        'reviews' => 'reviews',
        'blog' => 'blog',
        'privacy-policy' => 'privacy-policy',
        'terms-conditions' => 'terms-and-conditions',
        'cookies-policy' => 'cookies-policy',
        'complaints-policy-and-procedure' => 'complaints-policy-and-procedure',
        'attendance-and-punctuality-policy' => 'attendance-and-punctuality-policy',
    ];

    /** @var array<string, string> */
    protected static array $courseAliasToSlug = [
        'hospitality-management-and-advanced-food-science-600-hours' => 'hospitality-management-advanced-food-science-course',
        'advanced-professional-chef-course-550-hours' => 'advanced-professional-chef-course',
        'michelin-star-chef-course-300-hours' => 'michelin-star-chef-course',
        'professional-chef-course-325-hours' => 'professional-chef-course',
        'kosher-professional-chef-course-400-hours' => 'kosher-professional-chef-course',
        'halal-advanced-professional-chef-course-550-hours' => 'halal-advanced-professional-chef',
        'professional-chef-course-italian-cuisine-325-hours' => 'professional-chef-italian-cuisine',
        'professional-patisserie-and-confectionery-course-840-hours' => 'professional-patisserie',
        'introduction-to-the-professional-chef-course-80-hours' => 'introduction-professional-chef',
        'advanced-cooking-techniques-80-hours' => 'advanced-cooking-techniques',
        'intensive-professional-pastry-programme-80-hours' => 'intensive-professional-pastry',
    ];

    public static function extractDir(): string
    {
        return storage_path('app/joomla-extract');
    }

    public static function cleanJoomlaContent(string $content): string
    {
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $content = preg_replace('/\{[^}]+\}/', '', $content) ?? $content;
        $content = preg_replace('/\[row\]|\[\/row\]|\[col[^\]]*\]|\[\/col\]/', '', $content) ?? $content;
        $content = preg_replace('/<!--.*?-->/s', '', $content) ?? $content;
        $content = preg_replace('/\s+/u', ' ', $content) ?? $content;

        return trim($content);
    }

    public static function normalizeContentUrls(string $content): string
    {
        $langPattern = implode('|', LegacyLocaleMap::legacyLanguagePrefixes());

        $content = preg_replace(
            '#https?://(?:www\.)?culinaacademy\.com/(?:'.$langPattern.')/#i',
            '/',
            $content,
        ) ?? $content;
        $content = preg_replace('#https?://(?:www\.)?culinaacademy\.com/#i', '/', $content) ?? $content;
        $content = preg_replace(
            '#/(?:'.$langPattern.')/([a-z0-9\-/%]+)\.html#i',
            '/$1',
            $content,
        ) ?? $content;
        $content = preg_replace('#/images/#', '/uploads/joomla/images/', $content) ?? $content;
        $content = preg_replace('#\.html(["\'])#', '$1', $content) ?? $content;

        return CulinaAcademy::fixContentUrls($content);
    }

    public static function parseLegacyPublicHtml(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html) ?? $html;

        $body = '';
        if (preg_match('/<section[^>]*class="[^"]*entry-content[^"]*"[^>]*>(.*?)<\/section>/is', $html, $match)) {
            $body = $match[1];
        } elseif (preg_match('/<div[^>]*class="[^"]*item-page[^"]*"[^>]*>(.*)/is', $html, $match)) {
            $body = $match[1];
        }

        if ($body === '') {
            return '';
        }

        $body = preg_replace('/<header class="entry-header">.*?<\/header>/is', '', $body) ?? $body;
        $body = preg_replace('/<ul class="unstyled actions">.*?<\/ul>/is', '', $body) ?? $body;
        $body = preg_replace('/<div class="Zmargin-bottom-full">.*?<\/div>/is', '', $body) ?? $body;
        $body = preg_replace('/<h2[^>]*>\s*To reserve your place.*?<\/h2>.*?(?=<h[23]|$)/is', '', $body) ?? $body;
        $body = preg_replace('/<h3[^>]*>\s*Gallery\s*<\/h3>.*/is', '', $body) ?? $body;
        $body = preg_replace('/<\/footer>.*$/is', '', $body) ?? $body;
        $body = preg_replace('/<\/article>.*$/is', '', $body) ?? $body;

        return static::normalizeContentUrls(trim($body));
    }

    public static function fetchLegacyPublicHtml(string $legacyPath, ?string $lang = 'en'): ?string
    {
        $lang = PolylangService::normalizeCode($lang ?? 'en');
        $legacyPath = trim($legacyPath, '/');
        $base = rtrim(LegacySiteSyncService::LEGACY_BASE_URL, '/').'/'.$lang;
        $url = $legacyPath !== '' ? $base.'/'.$legacyPath.'.html' : $base.'/';

        $response = Http::timeout(30)->get($url);

        return $response->successful() ? $response->body() : null;
    }

    public static function findEnglishPageBySlug(string $slug): ?Post
    {
        return PolylangService::findPageBySlug($slug, 'en');
    }

    public static function findEnglishCourseBySlug(string $slug): ?Post
    {
        return PolylangService::findCourseBySlug($slug, 'en');
    }

    public static function courseSlugFromAlias(string $alias): ?string
    {
        $alias = strtolower(trim($alias));

        if (isset(static::$courseAliasToSlug[$alias])) {
            return static::$courseAliasToSlug[$alias];
        }

        if (preg_match('/^(.+)-\d+-hours$/', $alias, $match)) {
            return $match[1];
        }

        return null;
    }

    public static function pageSlugFromAlias(string $alias): ?string
    {
        $alias = strtolower(trim($alias));

        return static::PAGE_ALIAS_MAP[$alias] ?? null;
    }

    public static function isSkippableArticle(string $title, string $alias): bool
    {
        if ($title === '' || $alias === '') {
            return true;
        }

        if (preg_match('/^(Category:|Uncategorised|Thank you$)/i', $title)) {
            return true;
        }

        if (preg_match('/-(2|3|4|5)$/', $alias)) {
            return true;
        }

        return false;
    }

    public static function updatePostContent(Post $post, string $content): bool
    {
        $content = static::cleanJoomlaContent($content);
        $content = static::normalizeContentUrls($content);

        if ($content === '' || $content === ($post->post_content ?? '')) {
            return false;
        }

        DB::table('wp_posts')->where('ID', $post->ID)->update([
            'post_content' => $content,
            'post_modified' => now(),
            'post_modified_gmt' => now()->utc(),
        ]);

        AcfFieldService::clearCache($post->ID);

        return true;
    }

    public static function updateCourseDescription(Post $course, string $content): bool
    {
        $content = static::cleanJoomlaContent($content);
        $content = static::normalizeContentUrls($content);

        if ($content === '' || CourseDisplayService::isMetadataOnlyDescription($content)) {
            return false;
        }

        $current = (string) AcfFieldService::getField($course->ID, 'ca_course_description');
        if ($current === $content) {
            return false;
        }

        if ($current !== ''
            && ! CourseDisplayService::isMetadataOnlyDescription($current)
            && CourseDisplayService::plainTextLength($current) >= CourseDisplayService::plainTextLength($content)) {
            return false;
        }

        $meta = PostMeta::query()
            ->where('post_id', $course->ID)
            ->where('meta_key', 'ca_course_description')
            ->first();

        if ($meta) {
            $meta->meta_value = $content;
            $meta->save();
        } else {
            PostMeta::query()->create([
                'post_id' => $course->ID,
                'meta_key' => 'ca_course_description',
                'meta_value' => $content,
            ]);
        }

        AcfFieldService::clearCache($course->ID);

        return true;
    }

    /** @return array{updated:int,skipped:int,failed:int,results:array<int,array<string,mixed>>} */
    public static function syncLegacyPublicPages(): array
    {
        $updated = 0;
        $skipped = 0;
        $failed = 0;
        $results = [];

        foreach (LegacyLocaleMap::languages() as $lang) {
            foreach (static::LEGACY_PAGE_PATHS as $englishLegacyPath => $slug) {
                $page = PolylangService::findPageBySlug($slug, $lang);
                if (! $page) {
                    $results[] = [
                        'type' => 'page',
                        'slug' => $slug,
                        'lang' => $lang,
                        'updated' => false,
                        'error' => 'Page not found in DB',
                    ];
                    $failed++;

                    continue;
                }

                $legacyPath = LegacyLocaleMap::legacyPathForSlug($slug, $lang)
                    ?? LegacyLocaleMap::legacyPathForEnglishPath($englishLegacyPath, $lang);

                if ($legacyPath === null) {
                    $results[] = [
                        'type' => 'page',
                        'slug' => $slug,
                        'lang' => $lang,
                        'updated' => false,
                        'error' => null,
                        'skipped' => true,
                    ];
                    $skipped++;

                    continue;
                }

                $html = static::fetchLegacyPublicHtml($legacyPath, $lang);
                if ($html === null) {
                    $results[] = [
                        'type' => 'page',
                        'slug' => $slug,
                        'lang' => $lang,
                        'updated' => false,
                        'error' => 'Failed to fetch legacy page',
                    ];
                    $failed++;

                    continue;
                }

                $content = static::parseLegacyPublicHtml($html);
                if ($content === '') {
                    $results[] = [
                        'type' => 'page',
                        'slug' => $slug,
                        'lang' => $lang,
                        'updated' => false,
                        'error' => 'No content extracted',
                    ];
                    $failed++;

                    continue;
                }

                $changed = static::updatePostContent($page, $content);
                $results[] = [
                    'type' => 'page',
                    'slug' => $slug,
                    'lang' => $lang,
                    'updated' => $changed,
                    'error' => null,
                ];
                $changed ? $updated++ : $skipped++;
            }
        }

        return compact('updated', 'skipped', 'failed', 'results');
    }

    /** @return array{updated:int,skipped:int,failed:int,imported:int,results:array<int,array<string,mixed>>} */
    public static function syncFromExtract(?string $sourceDir = null): array
    {
        $sourceDir = $sourceDir ?: static::extractDir();
        $articlesDir = $sourceDir.'/articles';

        $updated = 0;
        $skipped = 0;
        $failed = 0;
        $imported = 0;
        $results = [];

        if (! is_dir($articlesDir)) {
            return [
                'updated' => 0,
                'skipped' => 0,
                'failed' => 1,
                'imported' => 0,
                'results' => [['error' => "Missing extract directory: {$articlesDir}"]],
            ];
        }

        $existingSlugs = Post::query()->pluck('post_name')->map(fn ($s) => strtolower((string) $s))->all();
        $maxId = (int) (Post::query()->max('ID') ?? 0);
        $nextId = $maxId + 1;

        foreach (glob($articlesDir.'/*.json') as $file) {
            $data = json_decode((string) file_get_contents($file), true);
            if (! is_array($data)) {
                $failed++;

                continue;
            }

            $title = trim(html_entity_decode((string) ($data['title'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $alias = strtolower(trim((string) ($data['alias'] ?? '')));
            $content = static::cleanJoomlaContent((string) ($data['fulltext'] ?? $data['introtext'] ?? ''));

            if (static::isSkippableArticle($title, $alias) || $content === '') {
                $skipped++;

                continue;
            }

            if ($pageSlug = static::pageSlugFromAlias($alias)) {
                $page = static::findEnglishPageBySlug($pageSlug);
                if ($page && static::updatePostContent($page, $content)) {
                    $updated++;
                    $results[] = ['type' => 'page', 'alias' => $alias, 'slug' => $pageSlug, 'updated' => true];
                } else {
                    $skipped++;
                }

                continue;
            }

            if ($courseSlug = static::courseSlugFromAlias($alias)) {
                $course = static::findEnglishCourseBySlug($courseSlug);
                if ($course && static::updateCourseDescription($course, $content)) {
                    $updated++;
                    $results[] = ['type' => 'course', 'alias' => $alias, 'slug' => $courseSlug, 'updated' => true];
                } else {
                    $skipped++;
                }

                continue;
            }

            if (in_array($alias, $existingSlugs, true)) {
                $post = Post::query()->where('post_name', $alias)->where('post_type', 'post')->first();
                if ($post && static::updatePostContent($post, $content)) {
                    $updated++;
                    $results[] = ['type' => 'blog', 'alias' => $alias, 'updated' => true];
                } else {
                    $skipped++;
                }

                continue;
            }

            $postId = $nextId++;
            DB::table('wp_posts')->insert([
                'ID' => $postId,
                'post_author' => 1,
                'post_date' => now(),
                'post_date_gmt' => now()->utc(),
                'post_content' => static::normalizeContentUrls($content),
                'post_title' => $title,
                'post_excerpt' => '',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_name' => $alias,
                'post_modified' => now(),
                'post_modified_gmt' => now()->utc(),
                'post_type' => 'post',
                'guid' => url('/blog/'.$alias),
            ]);

            $existingSlugs[] = $alias;
            $imported++;
            $results[] = ['type' => 'blog', 'alias' => $alias, 'imported' => true, 'id' => $postId];
        }

        return compact('updated', 'skipped', 'failed', 'imported', 'results');
    }

    /** @return array<string, mixed> */
    public static function runFullContentSync(): array
    {
        $courses = LegacySiteSyncService::syncAllLegacyCourses();
        $pages = static::syncLegacyPublicPages();
        $extract = static::syncFromExtract();
        $meta = CourseDisplayService::fixEnglishCourseMeta();

        SiteCacheService::clearResponseCache();

        return [
            'courses' => $courses,
            'pages' => $pages,
            'extract' => $extract,
            'english_meta' => $meta,
        ];
    }
}
