<?php

namespace App\Services;

use App\Models\Post;

class OnlineCourseService
{
    public const INTRODUCTION_SLUG = 'introduction-to-the-professional-chef-online-course';

    public const MICHELIN_SLUG = 'michelin-star-advanced-techniques-online-course';

    /** @var array<int, string> */
    public const LISTING_SLUGS = [
        'online-courses',
        'cours-en-ligne',
        'cursos-en-linea',
        'corsi-online',
        'zaixian-kecheng',
        'zaixian-kecheng-tw',
        // Legacy Chinese / URL-encoded slugs (pre-ASCII fix)
        '在线课程',
        '線上課程',
        '%e5%9c%a8%e7%ba%bf%e8%af%be%e7%a8%8b',
        '%e7%b7%9a%e4%b8%8a%e8%aa%b2%e7%a8%8b',
        '%E5%9C%A8%E7%BA%BF%E8%AF%BE%E7%A8%8B',
        '%E7%B7%9A%E4%B8%8A%E8%AA%B2%E7%A8%8B',
    ];

    public static function isListingSlug(string $slug): bool
    {
        $slug = self::normalizeListingSlug($slug);

        return in_array($slug, self::LISTING_SLUGS, true)
            || in_array(rawurldecode($slug), self::LISTING_SLUGS, true);
    }

    public static function normalizeListingSlug(string $slug): string
    {
        $slug = trim($slug, '/');
        // Laravel may pass already-decoded Chinese; DB may still have percent-encoding.
        $decoded = rawurldecode($slug);

        return $decoded !== '' ? $decoded : $slug;
    }

    /**
     * Canonical ASCII listing slug for URLs (used after Chinese pages were normalized).
     */
    public static function canonicalListingSlug(?string $lang = null): string
    {
        $lang = PolylangService::normalizeCode($lang ?? PolylangService::currentLanguage());
        $page = self::listingPage($lang);
        if ($page && $page->post_name !== '') {
            // Prefer ASCII slug on the page record.
            if (preg_match('#^[a-z0-9\-]+$#', $page->post_name)) {
                return $page->post_name;
            }
        }

        return match ($lang) {
            'fr' => 'cours-en-ligne',
            'es' => 'cursos-en-linea',
            'it' => 'corsi-online',
            'zh' => 'zaixian-kecheng',
            'zh-tw' => 'zaixian-kecheng-tw',
            default => 'online-courses',
        };
    }

    public static function isCourseSlug(string $slug): bool
    {
        return in_array(trim($slug, '/'), [self::INTRODUCTION_SLUG, self::MICHELIN_SLUG], true);
    }

    public static function listingPage(?string $lang = null): ?Post
    {
        $lang = PolylangService::normalizeCode($lang ?? PolylangService::currentLanguage());

        $enPage = Post::with('meta')
            ->where('post_name', 'online-courses')
            ->where('post_type', 'page')
            ->published()
            ->first();

        if (! $enPage) {
            return null;
        }

        if ($lang === 'en') {
            return $enPage;
        }

        $translatedId = PolylangService::getTranslationId($enPage->ID, $lang);

        // No English fallback — listing page must exist in the requested language.
        return $translatedId
            ? Post::with('meta')->published()->find($translatedId)
            : null;
    }

    public static function listingPath(?string $lang = null): string
    {
        $lang = PolylangService::normalizeCode($lang ?? PolylangService::currentLanguage());
        $page = self::listingPage($lang);

        if ($page) {
            // Always emit ASCII listing path even if DB still has a legacy encoded slug.
            return cal_localized_path('/'.self::canonicalListingSlug($lang), $lang);
        }

        return cal_localized_path('/'.self::canonicalListingSlug($lang), $lang);
    }

    public static function detailPath(string $courseSlug, ?string $lang = null): string
    {
        $lang = PolylangService::normalizeCode($lang ?? PolylangService::currentLanguage());
        $listingSlug = self::canonicalListingSlug($lang);

        return cal_localized_path('/'.$listingSlug.'/'.trim($courseSlug, '/'), $lang);
    }

    public static function courseSlugFromUrl(string $url): ?string
    {
        $url = strtolower(trim($url));
        if ($url === '') {
            return null;
        }

        if (! str_contains($url, '://') && preg_match('#^([a-z0-9.-]+\\.[a-z]{2,})(/.*)?$#i', $url)) {
            $url = 'https://'.$url;
        }

        $path = strtolower(parse_url($url, PHP_URL_PATH) ?? $url);

        if (str_contains($path, 'introduction-to-professional-chef')
            || str_contains($path, 'introduction-to-the-professional-chef')) {
            return self::INTRODUCTION_SLUG;
        }

        if (str_contains($path, 'michelin-star-advanced-techniques')) {
            return self::MICHELIN_SLUG;
        }

        return null;
    }

    public static function courseSlugFromCard(array $course): ?string
    {
        $fromUrl = self::courseSlugFromUrl((string) ($course['link_url'] ?? ''));
        if ($fromUrl) {
            return $fromUrl;
        }

        $titleSlug = strtolower((string) ($course['title'] ?? ''));
        if (str_contains($titleSlug, 'introduction') || str_contains($titleSlug, 'introdu')) {
            return self::INTRODUCTION_SLUG;
        }

        if (str_contains($titleSlug, 'michelin')) {
            return self::MICHELIN_SLUG;
        }

        return null;
    }

    /**
     * @return array{
     *     slug:string,
     *     title:string,
     *     image:string,
     *     description:string,
     *     price:string,
     *     duration:string,
     *     features:string,
     *     content:string,
     *     listing_page:Post,
     *     content_post:?Post
     * }|null
     */
    public static function resolve(string $courseSlug, ?string $lang = null): ?array
    {
        if (! self::isCourseSlug($courseSlug)) {
            return null;
        }

        $index = $courseSlug === self::INTRODUCTION_SLUG ? 0 : 1;
        $listingPage = self::listingPage($lang);

        if (! $listingPage) {
            return null;
        }

        $previousPostId = CulinaAcademy::getTheID();
        cal_bind_post($listingPage);

        $courses = cal_page_field('cal_online_courses', []);
        $course = is_array($courses) ? ($courses[$index] ?? null) : null;

        if (! is_array($course) || empty($course['title'])) {
            if ($previousPostId) {
                $restore = Post::find($previousPostId);
                if ($restore) {
                    cal_bind_post($restore);
                }
            }

            return null;
        }

        $contentPost = Post::with('meta')
            ->where('post_name', $courseSlug)
            ->where('post_type', 'post')
            ->published()
            ->first();

        $content = $contentPost?->post_content
            ? CulinaAcademy::fixContentUrls($contentPost->post_content)
            : self::buildIntroductionContent($course, $lang);

        if ($previousPostId && $previousPostId !== $listingPage->ID) {
            $restore = Post::find($previousPostId);
            if ($restore) {
                cal_bind_post($restore);
            }
        }

        return [
            'slug' => $courseSlug,
            'title' => (string) $course['title'],
            'image' => cal_image_url($course['image'] ?? ''),
            'description' => (string) ($course['description'] ?? ''),
            'price' => (string) ($course['price'] ?? ''),
            'duration' => (string) ($course['duration'] ?? ''),
            'features' => (string) ($course['features'] ?? ''),
            'content' => $content,
            'listing_page' => $listingPage,
            'content_post' => $contentPost,
        ];
    }

    protected static function buildIntroductionContent(array $course, ?string $lang = null): string
    {
        $lang = PolylangService::normalizeCode($lang ?? PolylangService::currentLanguage());
        $description = (string) ($course['description'] ?? '');
        $price = esc_html((string) ($course['price'] ?? ''));
        $duration = esc_html((string) ($course['duration'] ?? ''));
        $features = esc_html((string) ($course['features'] ?? ''));

        $overview = $lang === 'en'
            ? '<p>Our Introduction to Professional Chef course will provide you with guidance on the correct tools, equipment, skills and techniques necessary for a career as a Professional Chef.</p>'
                .'<p>Following the successful completion of your online course you will become certified and be presented with your online Diploma from Culina Academy which is internationally recognised and accredited by ASIC (Accreditation Service for International Colleges).</p>'
                .'<p>This course consists of many modules and will cover everything from knife techniques, to fish, meat &amp; poultry fabrication, stocks &amp; sauces and pasta making.</p>'
                .'<p>All of our modules are supported with reference documentation which you will be able to access at any time during the duration of the course.</p>'
            : '';

        $summary = '<div class="cal-online-detail-summary">'
            .($price !== '' ? '<p><strong>Fees:</strong> '.$price.'</p>' : '')
            .($duration !== '' ? '<p>'.$duration.'</p>' : '')
            .($features !== '' ? '<p>'.$features.'</p>' : '')
            .'</div>';

        return $overview.$description.$summary;
    }
}
