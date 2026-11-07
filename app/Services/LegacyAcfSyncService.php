<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostMeta;
use Illuminate\Support\Facades\DB;

class LegacyAcfSyncService
{
    /** @var array<string, array{slug:string,legacy_path:string,template:string}> */
    public const TEMPLATE_PAGES = [
        'home' => [
            'slug' => 'home',
            'legacy_path' => '',
            'template' => 'template-home.php',
        ],
        'about-us' => [
            'slug' => 'about-us',
            'legacy_path' => 'about-us',
            'template' => 'template-about.php',
        ],
        'faqs' => [
            'slug' => 'faqs',
            'legacy_path' => 'about-us/frequently-asked-questions',
            'template' => 'template-faq.php',
        ],
        'reviews' => [
            'slug' => 'reviews',
            'legacy_path' => 'reviews',
            'template' => 'template-reviews.php',
        ],
        'restaurant-partners' => [
            'slug' => 'restaurant-partners',
            'legacy_path' => 'restaurant-partners',
            'template' => 'template-restaurant-partners.php',
        ],
        'hospitality-development' => [
            'slug' => 'hospitality-development',
            'legacy_path' => 'about-us/our-schools',
            'template' => 'template-our-school.php',
        ],
        'careers' => [
            'slug' => 'careers',
            'legacy_path' => 'about-us/careers',
            'template' => 'template-careers.php',
        ],
        'how-to-apply' => [
            'slug' => 'how-to-apply',
            'legacy_path' => 'about-us/apply-now',
            'template' => 'template-how-to-apply.php',
        ],
        'online-courses' => [
            'slug' => 'online-courses',
            'legacy_path' => 'online-courses',
            'template' => 'template-online-courses.php',
        ],
        'cooking-courses' => [
            'slug' => 'cooking-courses',
            'legacy_path' => 'cooking-courses',
            'template' => 'template-cooking-courses.php',
        ],
    ];

    /** @return array{updated:int,skipped:int,failed:int,url_fixes:int,results:array<int,array<string,mixed>>} */
    public static function syncAllTemplateAcf(): array
    {
        $updated = 0;
        $skipped = 0;
        $failed = 0;
        $results = [];

        $urlFixes = static::migrateAcfUrls();

        foreach (LegacyLocaleMap::languages() as $lang) {
            foreach (static::TEMPLATE_PAGES as $key => $config) {
                $page = PolylangService::findPageBySlug($config['slug'], $lang);
                if (!$page) {
                    $results[] = [
                        'page' => $key,
                        'lang' => $lang,
                        'updated' => false,
                        'error' => null,
                        'skipped' => true,
                    ];
                    $skipped++;
                    continue;
                }

                $legacyPath = LegacyLocaleMap::legacyPathForSlug($config['slug'], $lang);
                if ($legacyPath === null && !in_array($key, ['home', 'reviews', 'online-courses'], true)) {
                    $results[] = [
                        'page' => $key,
                        'lang' => $lang,
                        'updated' => false,
                        'error' => null,
                        'skipped' => true,
                    ];
                    $skipped++;
                    continue;
                }

                try {
                    $changed = match ($key) {
                        'restaurant-partners' => static::syncRestaurantPartners($page, $lang, $legacyPath),
                        'about-us' => static::syncAboutPage($page, $lang, $legacyPath),
                        'hospitality-development' => static::syncOurSchoolPage($page, $lang, $legacyPath),
                        'faqs' => static::syncFaqPage($page, $lang, $legacyPath),
                        'careers' => static::syncCareersPage($page, $lang, $legacyPath),
                        'how-to-apply' => static::syncHowToApplyPage($page, $lang, $legacyPath),
                        'online-courses' => static::syncOnlineCoursesPage($page, $lang),
                        'cooking-courses' => static::syncCookingCoursesPage($page, $lang, $legacyPath),
                        default => static::syncGenericTemplatePage($page, $legacyPath ?? '', $lang),
                    };

                    $results[] = ['page' => $key, 'lang' => $lang, 'updated' => $changed, 'error' => null];
                    $changed ? $updated++ : $skipped++;
                } catch (\Throwable $e) {
                    $results[] = [
                        'page' => $key,
                        'lang' => $lang,
                        'updated' => false,
                        'error' => $e->getMessage(),
                    ];
                    $failed++;
                }
            }
        }

        SiteCacheService::clearResponseCache();

        return [
            'updated' => $updated,
            'skipped' => $skipped,
            'failed' => $failed,
            'url_fixes' => $urlFixes,
            'results' => $results,
        ];
    }

    public static function migrateAcfUrls(): int
    {
        $rows = PostMeta::query()
            ->where('meta_key', 'like', 'cal%')
            ->where(function ($q) {
                $q->where('meta_value', 'like', '%example.com%')
                    ->orWhere('meta_value', 'like', '%new.culinaacademy%')
                    ->orWhere('meta_value', 'like', '%/images/%')
                    ->orWhere('meta_value', 'like', '%/en/%')
                    ->orWhere('meta_value', 'like', '%/fr/%')
                    ->orWhere('meta_value', 'like', '%/es/%')
                    ->orWhere('meta_value', 'like', '%/it/%')
                    ->orWhere('meta_value', 'like', '%/zh/%');
            })
            ->get(['meta_id', 'post_id', 'meta_key', 'meta_value'])
            ->filter(fn ($row) => str_starts_with((string) $row->meta_key, 'cal_'));

        $fixes = 0;
        $touchedPosts = [];

        foreach ($rows as $row) {
            $migrated = static::migrateMetaValue((string) $row->meta_value);
            if ($migrated === $row->meta_value) {
                continue;
            }

            DB::table('wp_postmeta')
                ->where('meta_id', $row->meta_id)
                ->update(['meta_value' => $migrated]);

            $touchedPosts[(int) $row->post_id] = true;
            $fixes++;
        }

        foreach (array_keys($touchedPosts) as $postId) {
            AcfFieldService::clearCache($postId);
        }

        return $fixes;
    }

    protected static function migrateMetaValue(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        if (str_starts_with($value, 'a:') || str_starts_with($value, 's:')) {
            $decoded = @unserialize($value);
            if ($decoded !== false || $value === serialize(false)) {
                $migrated = static::migrateValueRecursive($decoded);

                return $migrated === $decoded ? $value : serialize($migrated);
            }
        }

        if (preg_match('/@[^@]+culinaacademy\.com/i', $value)) {
            return $value;
        }

        if (str_contains($value, 'example.com')
            || str_contains($value, 'new.culinaacademy')
            || str_contains($value, '/images/')
            || preg_match('#/(?:en|fr|es|it|zh|zh-tw)/[a-z0-9\-/]+\.html#i', $value)) {
            $normalized = JoomlaContentSyncService::normalizeContentUrls($value);

            return AcfFieldService::migrateUrl($normalized);
        }

        return $value;
    }

    protected static function migrateValueRecursive(mixed $value): mixed
    {
        if (is_string($value)) {
            if (preg_match('/@[^@]+culinaacademy\.com/i', $value)) {
                return $value;
            }

            if (!str_contains($value, 'example.com')
                && !str_contains($value, 'new.culinaacademy')
                && !str_contains($value, '/images/')
                && !preg_match('#/(?:en|fr|es|it|zh|zh-tw)/[a-z0-9\-/]+\.html#i', $value)) {
                return $value;
            }

            if (isset($value[0]) && $value[0] === '/') {
                return AcfFieldService::migrateUrl($value);
            }

            return AcfFieldService::migrateUrl(JoomlaContentSyncService::normalizeContentUrls($value));
        }

        if (!is_array($value)) {
            return $value;
        }

        $changed = false;
        foreach ($value as $key => $item) {
            $migrated = static::migrateValueRecursive($item);
            if ($migrated !== $item) {
                $value[$key] = $migrated;
                $changed = true;
            }
        }

        return $changed ? $value : $value;
    }

    protected static function syncCareersPage(Post $page, string $lang, ?string $legacyPath): bool
    {
        if ($legacyPath === null) {
            return false;
        }

        $html = JoomlaContentSyncService::fetchLegacyPublicHtml($legacyPath, $lang);
        if ($html === null) {
            return false;
        }

        $body = static::extractEntryContent($html);
        if ($body === '') {
            return false;
        }

        $changed = false;
        $paragraphs = static::extractParagraphs($body);
        $introParagraphs = array_values(array_filter(
            $paragraphs,
            fn ($text) => strlen($text) > 80 && !preg_match('/^(Andy|Registered Office|Phone:)/i', $text),
        ));

        if ($introParagraphs !== []) {
            $introText = implode(' ', array_slice($introParagraphs, 0, 3));
            $currentIntro = trim((string) AcfFieldService::getField($page->ID, 'cal_careers_intro_text', ''));
            if ($currentIntro === '' && $introText !== '') {
                PostMetaWriter::set($page->ID, 'cal_careers_intro_text', $introText);
                $changed = true;
            }

            if ($lang === 'en' && trim((string) AcfFieldService::getField($page->ID, 'cal_careers_intro_heading', '')) === '') {
                PostMetaWriter::set($page->ID, 'cal_careers_intro_heading', 'Your Global Career Starts Here');
                $changed = true;
            }
        }

        $cards = AcfFieldService::getRepeater($page->ID, 'cal_careers_cards');
        if (count($cards) === 0) {
            $parsedCards = static::parseCareersCards($body);
            if ($parsedCards !== []) {
                PostMetaWriter::setRepeater($page->ID, 'cal_careers_cards', $parsedCards);
                $changed = true;
            }
        }

        if (trim((string) AcfFieldService::getField($page->ID, 'cal_careers_hero_title', '')) === '') {
            PostMetaWriter::set($page->ID, 'cal_careers_hero_title', $page->post_title ?: 'Careers');
            $changed = true;
        }

        return $changed;
    }

    /** @return array<int, array<string, string>> */
    protected static function parseCareersCards(string $html): array
    {
        if (!preg_match_all(
            '/<p[^>]*>\s*<img[^>]+alt="([^"]*)"[^>]*>.*?<\/p>\s*<p[^>]*>([^<]+)<\/p>\s*<p[^>]*>([^<]+)<\/p>/is',
            $html,
            $matches,
            PREG_SET_ORDER,
        )) {
            return [];
        }

        $cards = [];
        foreach ($matches as $match) {
            $title = trim(html_entity_decode(strip_tags($match[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'), " \t\n\r\0\x0B,");
            $text = trim(html_entity_decode(strip_tags($match[3]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($title === '' || preg_match('/^(Registered Office|Phone:)/i', $title)) {
                continue;
            }

            $cards[] = [
                'image' => '',
                'title' => rtrim($title, ','),
                'text' => $text,
            ];
        }

        return $cards;
    }

    protected static function syncHowToApplyPage(Post $page, string $lang, ?string $legacyPath): bool
    {
        if ($legacyPath === null) {
            return false;
        }

        $html = JoomlaContentSyncService::fetchLegacyPublicHtml($legacyPath, $lang);
        if ($html === null) {
            return false;
        }

        $body = static::extractEntryContent($html);
        if ($body === '') {
            return false;
        }

        $content = static::cleanLegacyWysiwyg($body);
        $changed = false;

        $currentContent = trim((string) AcfFieldService::getField($page->ID, 'cal_howto_content', ''));
        if ($currentContent === '' || strlen(strip_tags($currentContent)) < strlen(strip_tags($content)) * 0.5) {
            if ($currentContent !== $content) {
                PostMetaWriter::set($page->ID, 'cal_howto_content', $content);
                $changed = true;
            }
        }

        $heading = trim((string) AcfFieldService::getField($page->ID, 'cal_howto_hero_heading', ''));
        if ($heading === '') {
            PostMetaWriter::set($page->ID, 'cal_howto_hero_heading', $page->post_title ?: 'How to Apply');
            $changed = true;
        }

        $enquireUrl = trim((string) AcfFieldService::getField($page->ID, 'cal_howto_enquire_url', ''));
        $targetUrl = PolylangService::localizedPath('/course-enrollment', $lang);
        if ($enquireUrl === '' || str_contains($enquireUrl, 'example.com')) {
            PostMetaWriter::set($page->ID, 'cal_howto_enquire_url', $targetUrl);
            $changed = true;
        }

        if ($lang === 'en' && trim((string) AcfFieldService::getField($page->ID, 'cal_howto_enquire_text', '')) === '') {
            PostMetaWriter::set($page->ID, 'cal_howto_enquire_text', 'Enquire now');
            $changed = true;
        }

        return $changed;
    }

    protected static function syncOnlineCoursesPage(Post $page, string $lang): bool
    {
        $courses = AcfFieldService::getRepeater($page->ID, 'cal_online_courses');
        $changed = false;

        if ($courses === [] && $lang === 'en') {
            $courses = static::defaultOnlineCourses($lang);
            if ($courses !== []) {
                PostMetaWriter::setRepeater($page->ID, 'cal_online_courses', $courses);
                $changed = true;
            }
        } elseif ($courses !== []) {
            $updated = [];
            foreach ($courses as $course) {
                $linkUrl = trim((string) ($course['link_url'] ?? ''));
                $slug = OnlineCourseService::courseSlugFromUrl($linkUrl)
                    ?: OnlineCourseService::courseSlugFromCard($course);
                if ($slug) {
                    $localized = OnlineCourseService::detailPath($slug, $lang);
                    if ($linkUrl !== $localized) {
                        $course['link_url'] = $localized;
                        $changed = true;
                    }
                } elseif ($linkUrl !== '') {
                    $migrated = AcfFieldService::migrateUrl($linkUrl);
                    if ($migrated !== $linkUrl) {
                        $course['link_url'] = $migrated;
                        $changed = true;
                    }
                }
                $updated[] = $course;
            }

            if ($changed) {
                PostMetaWriter::setRepeater($page->ID, 'cal_online_courses', $updated);
            }
        }

        if (trim((string) AcfFieldService::getField($page->ID, 'cal_online_heading', '')) === '') {
            PostMetaWriter::set($page->ID, 'cal_online_heading', $page->post_title ?: 'Online Courses');
            $changed = true;
        }

        if ($lang === 'en' && trim((string) AcfFieldService::getField($page->ID, 'cal_online_subtitle', '')) === '') {
            PostMetaWriter::set(
                $page->ID,
                'cal_online_subtitle',
                'Flexible chef training you can complete from anywhere, at your own pace.',
            );
            $changed = true;
        }

        return $changed;
    }

    /** @return array<int, array<string, string>> */
    protected static function defaultOnlineCourses(string $lang = 'en'): array
    {
        return [
            [
                'image' => '',
                'title' => 'Introduction to the Professional Chef Online Course',
                'description' => '<p>The Culina Academy offers professional culinary education through practical training and selected online learning options.</p>',
                'price' => '£1,495',
                'duration' => '6 months to complete',
                'features' => 'Fully supported. Culinary reference document provided.',
                'link_url' => OnlineCourseService::detailPath(OnlineCourseService::INTRODUCTION_SLUG, $lang),
                'link_text' => 'See course details',
            ],
            [
                'image' => '',
                'title' => 'Michelin Star Advanced Techniques Online Course',
                'description' => '<p>The Culina Academy offers professional culinary education through practical training and selected online learning options.</p>',
                'price' => '£1,495',
                'duration' => 'Fully supported',
                'features' => '10% discount if both courses purchased together.',
                'link_url' => OnlineCourseService::detailPath(OnlineCourseService::MICHELIN_SLUG, $lang),
                'link_text' => 'See course details',
            ],
        ];
    }

    protected static function syncCookingCoursesPage(Post $page, string $lang, ?string $legacyPath): bool
    {
        if ($legacyPath === null) {
            return false;
        }

        $html = JoomlaContentSyncService::fetchLegacyPublicHtml($legacyPath, $lang);
        $changed = false;

        $heroTitle = trim((string) AcfFieldService::getField($page->ID, 'cal_cooking_courses_hero_title', ''));
        $heroText = trim((string) AcfFieldService::getField($page->ID, 'cal_cooking_courses_hero_text', ''));

        if ($html !== null) {
            if (preg_match('/<title>([^<]+)<\/title>/i', $html, $titleMatch)) {
                $legacyTitle = trim(html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $legacyTitle = preg_replace('/\s*[-|].*$/', '', $legacyTitle) ?? $legacyTitle;
                $legacyTitle = trim($legacyTitle);

                if ($heroTitle === '' && $legacyTitle !== '') {
                    PostMetaWriter::set($page->ID, 'cal_cooking_courses_hero_title', $legacyTitle);
                    $heroTitle = $legacyTitle;
                    $changed = true;
                }
            }

            if (preg_match('/<meta[^>]+name="description"[^>]+content="([^"]+)"/i', $html, $descMatch)) {
                $description = trim(html_entity_decode($descMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                if ($heroText === '' && $description !== '') {
                    PostMetaWriter::set($page->ID, 'cal_cooking_courses_hero_text', $description);
                    $changed = true;
                }
            }
        }

        if ($heroTitle === '') {
            PostMetaWriter::set($page->ID, 'cal_cooking_courses_hero_title', $page->post_title ?: 'Cooking Courses');
            $changed = true;
        }

        if ($lang === 'en' && $heroText === '') {
            PostMetaWriter::set(
                $page->ID,
                'cal_cooking_courses_hero_text',
                'Industry-leading culinary training in Your City — from intensive short programmes to comprehensive professional diplomas.',
            );
            $changed = true;
        }

        $introTitle = trim((string) AcfFieldService::getField($page->ID, 'cal_cooking_courses_intro_title', ''));
        if ($lang === 'en' && $introTitle === '') {
            PostMetaWriter::set($page->ID, 'cal_cooking_courses_intro_title', 'Professional Cooking Courses in Your City');
            $changed = true;
        }

        $introText = trim((string) AcfFieldService::getField($page->ID, 'cal_cooking_courses_intro_text', ''));
        if ($lang === 'en' && $introText === '') {
            PostMetaWriter::set(
                $page->ID,
                'cal_cooking_courses_intro_text',
                'Explore our practical chef training programmes, compare course options, and choose the professional cooking pathway that fits your goals.',
            );
            $changed = true;
        }

        return $changed;
    }

    protected static function cleanLegacyWysiwyg(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<span[^>]*id="cloak[^"]*"[^>]*>.*?<\/span>/is', 'enrolment@example.com', $html) ?? $html;
        $html = preg_replace('/This email address is being protected from spambots\.[^<]*/i', 'enrolment@example.com', $html) ?? $html;
        $html = preg_replace('/<\/footer>.*$/is', '', $html) ?? $html;
        $html = preg_replace('/<\/article>.*$/is', '', $html) ?? $html;

        return trim(JoomlaContentSyncService::normalizeContentUrls($html));
    }

    protected static function syncGenericTemplatePage(Post $page, string $legacyPath, string $lang): bool
    {
        return false;
    }

    protected static function syncFaqPage(Post $page, string $lang, ?string $legacyPath): bool
    {
        $categories = AcfFieldService::getRepeater($page->ID, 'cal_faq_categories');
        if (count($categories) > 0) {
            return false;
        }

        if ($legacyPath === null) {
            return false;
        }

        $html = JoomlaContentSyncService::fetchLegacyPublicHtml($legacyPath, $lang);
        if ($html === null) {
            return false;
        }

        $body = static::extractEntryContent($html);
        if ($body === '') {
            return false;
        }

        $questions = static::parseFaqQuestions($body);
        if ($questions === []) {
            return false;
        }

        $categoryTitle = match ($lang) {
            'fr' => 'Questions générales',
            'es' => 'Preguntas generales',
            'it' => 'Domande generali',
            'zh', 'zh-tw' => '常见问题',
            default => 'General Questions',
        };

        PostMetaWriter::setNestedRepeater($page->ID, 'cal_faq_categories', [
            [
                'title' => $categoryTitle,
                'questions' => $questions,
            ],
        ]);

        return true;
    }

    /** @return array<int, array{question:string,answer:string}> */
    protected static function parseFaqQuestions(string $html): array
    {
        $questions = [];
        $chunks = preg_split('/<p>\s*<strong>([^<]+)<\/strong>\s*<\/p>/i', $html, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];

        for ($i = 1; $i < count($chunks); $i += 2) {
            $question = trim(html_entity_decode(strip_tags((string) ($chunks[$i] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $answerHtml = (string) ($chunks[$i + 1] ?? '');
            $answerHtml = preg_replace('/<p>\s*<strong>[^<]+<\/strong>\s*<\/p>/i', '', $answerHtml, 1) ?? $answerHtml;
            $nextPos = strpos($answerHtml, '<p><strong>');
            if ($nextPos !== false) {
                $answerHtml = substr($answerHtml, 0, $nextPos);
            }

            $answer = trim(JoomlaContentSyncService::normalizeContentUrls($answerHtml));
            if ($question !== '' && $answer !== '') {
                $questions[] = ['question' => $question, 'answer' => $answer];
            }
        }

        return $questions;
    }

    protected static function syncAboutPage(Post $page, string $lang, ?string $legacyPath): bool
    {
        if ($legacyPath === null) {
            return false;
        }

        $html = JoomlaContentSyncService::fetchLegacyPublicHtml($legacyPath, $lang);
        if ($html === null) {
            return false;
        }

        $body = static::extractEntryContent($html);
        if ($body === '') {
            return false;
        }

        $paragraphs = static::extractParagraphs($body);
        if ($paragraphs === []) {
            return false;
        }

        $existing = AcfFieldService::getRepeater($page->ID, 'cal_about_story_paragraphs');
        $existingText = implode("\n", array_map(
            fn ($row) => trim((string) ($row['paragraph'] ?? '')),
            is_array($existing) ? $existing : [],
        ));
        $newText = implode("\n", $paragraphs);

        if ($existingText === $newText) {
            return false;
        }

        PostMetaWriter::setRepeater(
            $page->ID,
            'cal_about_story_paragraphs',
            array_map(fn ($text) => ['paragraph' => $text], $paragraphs),
        );

        return true;
    }

    protected static function syncOurSchoolPage(Post $page, string $lang, ?string $legacyPath): bool
    {
        if ($legacyPath === null) {
            return false;
        }

        $html = JoomlaContentSyncService::fetchLegacyPublicHtml($legacyPath, $lang);
        if ($html === null) {
            return false;
        }

        $body = static::extractEntryContent($html);
        $paragraphs = static::extractParagraphs($body);
        if ($paragraphs === []) {
            return false;
        }

        $intro = implode("\n\n", array_slice($paragraphs, 0, 2));
        $current = trim((string) AcfFieldService::getField($page->ID, 'cal_our_school_hero_intro', ''));

        if ($current === $intro) {
            return false;
        }

        PostMetaWriter::set($page->ID, 'cal_our_school_hero_intro', $intro);

        return true;
    }

    protected static function syncRestaurantPartners(Post $page, string $lang, ?string $legacyPath): bool
    {
        if ($legacyPath === null) {
            return false;
        }

        $html = JoomlaContentSyncService::fetchLegacyPublicHtml($legacyPath, $lang);
        if ($html === null) {
            return false;
        }

        $parsed = static::parseRestaurantArticles($html);
        if ($parsed === []) {
            return false;
        }

        $existing = AcfFieldService::getRepeater($page->ID, 'cal_rp_restaurants');
        $existingByName = [];
        foreach ($existing as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name !== '') {
                $existingByName[strtolower($name)] = $row;
            }
        }

        $merged = [];

        foreach ($parsed as $row) {
            $key = strtolower($row['name']);
            $prev = $existingByName[$key] ?? [];

            $merged[] = [
                'name' => $row['name'],
                'image' => $prev['image'] ?? '',
                'address' => $row['address'],
                'michelin' => $row['michelin'],
                'aa' => $row['aa'],
                'map_url' => $row['map_url'],
                'website_url' => $row['website_url'],
                'website_label' => $row['website_label'],
            ];
        }

        if (static::repeatersEqual($existing, $merged)) {
            return false;
        }

        PostMetaWriter::setRepeater($page->ID, 'cal_rp_restaurants', $merged);

        return true;
    }

    /** @return array<int, array<string, mixed>> */
    protected static function parseRestaurantArticles(string $html): array
    {
        if (!preg_match_all('/<article[^>]*>(.*?)<\/article>/is', $html, $matches)) {
            return [];
        }

        $restaurants = [];

        foreach ($matches[1] as $articleHtml) {
            if (!preg_match('/<h2[^>]*class="[^"]*entry-title[^"]*"[^>]*>.*?<a[^>]*>(.*?)<\/a>/is', $articleHtml, $titleMatch)
                && !preg_match('/<h2[^>]*class="[^"]*entry-title[^"]*"[^>]*>\s*([^<]+)/is', $articleHtml, $titleMatch)) {
                continue;
            }

            $name = trim(html_entity_decode(strip_tags($titleMatch[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($name === '') {
                continue;
            }

            $content = '';
            if (preg_match('/<section[^>]*class="[^"]*entry-content[^"]*"[^>]*>(.*?)<\/section>/is', $articleHtml, $contentMatch)) {
                $content = $contentMatch[1];
            }

            $address = static::extractRestaurantAddress($content, $name);
            $mapUrl = '';
            if (preg_match('/<a[^>]+href="([^"]+)"[^>]*>\s*View map\s*<\/a>/i', $content, $mapMatch)) {
                $mapUrl = AcfFieldService::migrateUrl(html_entity_decode($mapMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }

            $websiteUrl = '';
            $websiteLabel = '';
            if (preg_match('/<a[^>]+href="(https?:\/\/[^"]+)"[^>]*>\s*(www\.[^<]+)\s*<\/a>/i', $content, $siteMatch)) {
                $websiteUrl = trim($siteMatch[1]);
                $websiteLabel = trim($siteMatch[2]);
            } elseif (preg_match('/<a[^>]+href="(https?:\/\/[^"]+)"[^>]*>/i', $content, $siteMatch)) {
                $websiteUrl = trim($siteMatch[1]);
                $websiteLabel = rtrim(preg_replace('#^https?://#', '', $websiteUrl), '/');
            }

            $restaurants[] = [
                'name' => $name,
                'address' => $address,
                'michelin' => substr_count(strtolower($content), 'michelin_rose.gif'),
                'aa' => substr_count(strtolower($content), 'aa_rose.gif'),
                'map_url' => $mapUrl,
                'website_url' => $websiteUrl,
                'website_label' => $websiteLabel,
            ];
        }

        return $restaurants;
    }

    protected static function extractRestaurantAddress(string $content, string $name): string
    {
        $text = html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = trim($text);

        $text = preg_replace('/Michelin Stars:.*/is', '', $text) ?? $text;
        $text = preg_replace('/AA Rosettes:.*/is', '', $text) ?? $text;
        $text = preg_replace('/View map.*/is', '', $text) ?? $text;
        $text = trim($text);

        $lines = preg_split('/\s*(?:<br\s*\/?>|\n)\s*/i', $content) ?: [];
        $plainLines = [];
        foreach ($lines as $line) {
            $plain = trim(html_entity_decode(strip_tags($line), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($plain === '' || stripos($plain, 'Michelin') !== false || stripos($plain, 'AA Rosettes') !== false) {
                continue;
            }
            if (stripos($plain, 'View map') !== false || str_contains($plain, 'www.')) {
                continue;
            }
            $plainLines[] = $plain;
        }

        if ($plainLines !== []) {
            return implode("\n", $plainLines);
        }

        return $name;
    }

    protected static function extractEntryContent(string $html): string
    {
        if (preg_match('/<section[^>]*class="[^"]*entry-content[^"]*"[^>]*>(.*?)<\/section>/is', $html, $match)) {
            return trim($match[1]);
        }

        return JoomlaContentSyncService::parseLegacyPublicHtml($html);
    }

    /** @param array<int, array<string, mixed>> $left @param array<int, array<string, mixed>> $right */
    protected static function repeatersEqual(array $left, array $right): bool
    {
        if (count($left) !== count($right)) {
            return false;
        }

        foreach ($right as $index => $row) {
            $prev = $left[$index] ?? [];
            foreach (['name', 'address', 'michelin', 'aa', 'map_url', 'website_url', 'website_label'] as $field) {
                $a = trim((string) ($prev[$field] ?? ''));
                $b = trim((string) ($row[$field] ?? ''));
                if ($field === 'michelin' || $field === 'aa') {
                    $a = (string) (int) $a;
                    $b = (string) (int) $b;
                }
                if ($a !== $b) {
                    return false;
                }
            }
        }

        return true;
    }

    /** @return array<int, string> */
    protected static function extractParagraphs(string $html): array
    {
        if (!preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $html, $matches)) {
            return [];
        }

        $paragraphs = [];
        foreach ($matches[1] as $raw) {
            $text = trim(html_entity_decode(strip_tags($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($text !== '' && !preg_match('/^(Print|Email)$/i', $text)) {
                $paragraphs[] = $text;
            }
        }

        return $paragraphs;
    }
}