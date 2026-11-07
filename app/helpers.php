<?php

use App\Models\Post;
use App\Models\PostMeta;
use App\Services\AcfFieldService;
use App\Services\CulinaAcademy;
use App\Services\MenuService;

if (!function_exists('cal_get_field')) {
    function cal_get_field(string $name, int $postId = 0): mixed
    {
        if (!$postId) {
            $postId = get_the_ID();
        }
        return AcfFieldService::getField($postId, $name);
    }
}

if (!function_exists('cal_page_field')) {
    function cal_page_field(string $name, mixed $fallback = ''): mixed
    {
        $value = cal_get_field($name, get_the_ID());
        return ($value === '' || $value === null) ? $fallback : $value;
    }
}

if (!function_exists('cal_option')) {
    function cal_option(string $name, mixed $fallback = ''): mixed
    {
        $value = AcfFieldService::getOption($name);
        return ($value === '' || $value === null) ? $fallback : $value;
    }
}

if (!function_exists('cal_about_field')) {
    function cal_about_field(string $name): mixed
    {
        return cal_page_field($name);
    }
}

if (!function_exists('cal_image_url')) {
    function cal_image_url(mixed $image): string
    {
        return AcfFieldService::imageUrl($image);
    }
}

if (!function_exists('cal_image_src')) {
    /** @return array{url:string,width:int,height:int}|null */
    function cal_image_src(mixed $image, string $size = 'large'): ?array
    {
        return AcfFieldService::attachmentSrc($image, $size);
    }
}

if (!function_exists('cal_image_attrs')) {
    function cal_image_attrs(mixed $image, string $size = 'large'): string
    {
        return AcfFieldService::imageSizeAttrs($image, $size);
    }
}

if (!function_exists('cal_image_srcset')) {
    function cal_image_srcset(mixed $image, int $maxWidth = 1536): string
    {
        return AcfFieldService::attachmentSrcset($image, $maxWidth);
    }
}

if (!function_exists('cal_hero_image_src')) {
    /** @return array{url:string,width:int,height:int}|null */
    function cal_hero_image_src(mixed $image): ?array
    {
        return AcfFieldService::heroImageSrc($image);
    }
}

if (!function_exists('cal_migrate_url')) {
    function cal_migrate_url(string $url): string
    {
        return AcfFieldService::migrateUrl($url);
    }
}

if (!function_exists('cal_youtube_embed_url')) {
    /**
     * Convert any video URL (YouTube or direct mp4) to an embed URL.
     * For YouTube: returns embed URL with rel=0.
     * For direct video files: returns the file URL prefixed with "video:" for the modal to handle.
     */
    function cal_youtube_embed_url(string $url): string
    {
        if (preg_match('#(?:youtube\\.com/(?:watch\\?v=|embed/|shorts/)|youtu\\.be/)([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return 'https://www.youtube.com/@example' . $m[1] . '?rel=0&modestbranding=1&autoplay=1';
        }
        // Direct video file (mp4, webm, ogg)
        if (preg_match('#\\.(mp4|webm|ogg)(\\?.*)?$#i', $url)) {
            return 'video:' . $url;
        }
        return '';
    }
}

if (!function_exists('cal_oembed_html')) {
    /**
     * Convert ACF oEmbed values (URL or iframe HTML) into embed markup.
     */
    function cal_oembed_html(mixed $value): string
    {
        if (!is_string($value)) {
            return '';
        }

        $value = trim(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
        if ($value === '') {
            return '';
        }

        if (str_contains($value, '<iframe')) {
            return $value;
        }

        if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([a-zA-Z0-9_-]{11})#', $value, $matches)) {
            $id = $matches[1];

            return '<iframe src="https://www.youtube.com/@example' . esc_attr($id) . '?rel=0&modestbranding=1" title="YouTube video player" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>';
        }

        if (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $value, $matches)) {
            $id = $matches[1];

            return '<iframe src="https://player.vimeo.com/video/' . esc_attr($id) . '" title="Vimeo video player" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen loading="lazy"></iframe>';
        }

        return '';
    }
}

if (!function_exists('cal_render_page_banner')) {
    function cal_render_page_banner(string $class, string $title = ''): void
    {
        CulinaAcademy::renderPageBanner($class, $title ?: get_the_title());
    }
}

if (!function_exists('cal_render_breadcrumbs')) {
    function cal_render_breadcrumbs(string $class): void
    {
        CulinaAcademy::renderBreadcrumbs($class);
    }
}

if (!function_exists('cal_render_link')) {
    function cal_render_link(mixed $link, string $class = ''): void
    {
        CulinaAcademy::renderLink($link, $class);
    }
}

if (!function_exists('cal_i18n_text')) {
    function cal_i18n_text(string $string): string
    {
        return \App\Services\UiTranslationService::translate($string);
    }
}

if (!function_exists('cal_current_language')) {
    function cal_current_language(): string
    {
        return \App\Services\PolylangService::currentLanguage();
    }
}

if (!function_exists('cal_our_school_text_blocks')) {
    /**
     * Split section copy into the first paragraph and any following paragraphs.
     *
     * @return array{first:string,rest:array<int,string>}
     */
    function cal_our_school_text_blocks(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return ['first' => '', 'rest' => []];
        }

        if (stripos($text, '<p') !== false) {
            preg_match_all('/<p[^>]*>.*?<\/p>/is', $text, $matches);
            $paragraphs = array_values(array_filter(array_map('trim', $matches[0] ?? [])));

            if ($paragraphs !== []) {
                return [
                    'first' => (string) array_shift($paragraphs),
                    'rest' => $paragraphs,
                ];
            }
        }

        $paragraphs = preg_split('/\R\s*\R+/', $text);
        $paragraphs = array_values(array_filter(array_map('trim', $paragraphs ?: [])));

        return [
            'first' => (string) ($paragraphs[0] ?? $text),
            'rest' => array_slice($paragraphs, 1),
        ];
    }
}

if (!function_exists('cal_our_school_render_paragraph')) {
    function cal_our_school_render_paragraph(string $paragraph): string
    {
        $paragraph = trim($paragraph);
        if ($paragraph === '') {
            return '';
        }

        if (str_contains($paragraph, '<p')) {
            return $paragraph;
        }

        return nl2br(e($paragraph));
    }
}

if (!function_exists('cal_localized_path')) {
    function cal_localized_path(string $path, ?string $lang = null): string
    {
        return \App\Services\PolylangService::localizedPath($path, $lang);
    }
}

if (!function_exists('cal_localized_url')) {
    function cal_localized_url(int $postId, ?string $lang = null): string
    {
        return \App\Services\PolylangService::buildPostUrl($postId, $lang);
    }
}

if (!function_exists('cal_phone_icon')) {
    function cal_phone_icon(): string
    {
        return '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>';
    }
}

if (!function_exists('cal_email_icon')) {
    function cal_email_icon(): string
    {
        return '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>';
    }
}

if (!function_exists('cal_menu_icon')) {
    function cal_menu_icon(): string
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>';
    }
}

if (!function_exists('cal_close_icon')) {
    function cal_close_icon(): string
    {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
    }
}

if (!function_exists('get_the_title')) {
    function get_the_title(): string
    {
        return CulinaAcademy::getTheTitle();
    }
}

if (!function_exists('get_the_ID')) {
    function get_the_ID(): int
    {
        return CulinaAcademy::getTheID();
    }
}

if (!function_exists('esc_html')) {
    function esc_html(?string $s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr(?string $s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url(?string $s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_js')) {
    function esc_js(?string $s): string
    {
        return json_encode((string) $s, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?: '""';
    }
}

if (!function_exists('wp_get_attachment_image')) {
    function wp_get_attachment_image(int $id, string $size = 'full'): string
    {
        $url = AcfFieldService::attachmentUrl($id);
        return $url ? '<img src="' . esc_url($url) . '" alt="" loading="lazy">' : '';
    }
}

if (!function_exists('cal_render_primary_menu')) {
    function cal_render_primary_menu(): void
    {
        echo MenuService::primaryMenuHtml();
    }
}

if (!function_exists('cal_footer_map_url')) {
    function cal_footer_map_url(string $address): string
    {
        return 'https://maps.google.com/?q=' . urlencode($address);
    }
}

if (!function_exists('cal_format_price')) {
    function cal_format_price($val): string
    {
        return CulinaAcademy::formatPrice($val);
    }
}

if (!function_exists('cal_normalize_course_slug')) {
    function cal_normalize_course_slug(string $slug): string
    {
        static $aliases = [
            'professional-chef-course-325-hours' => 'professional-chef-course',
            'advanced-professional-chef-course-550-hours' => 'advanced-professional-chef-course',
            'michelin-star-chef-course-300-hours' => 'michelin-star-chef-course',
            'michelin-starred-chef-course' => 'michelin-star-chef-course',
            'introduction-to-the-professional-chef-course' => 'introduction-professional-chef',
            'introduction-to-the-professional-chef-course-80-hours' => 'introduction-professional-chef',
            'intro-professional-chef' => 'introduction-professional-chef',
            'kosher-professional-chef-course-400-hours' => 'kosher-professional-chef-course',
            'professional-patisserie-and-confectionery-course' => 'professional-patisserie',
            'professional-patisserie-confectionary' => 'professional-patisserie',
            'hospitality-management-and-advanced-food-science' => 'hospitality-management-advanced-food-science-course',
            'hospitality-management-and-advanced-food-science-600-hours' => 'hospitality-management-advanced-food-science-course',
            'hospitality-management-and-advanced-food-science-700-hours' => 'hospitality-management-advanced-food-science-course',
            'halal-advanced-professional-chef-course' => 'halal-advanced-professional-chef',
            'halal-advanced-professional-chef-course-550-hours' => 'halal-advanced-professional-chef',
            'intensive-professional-pastry-programme-80-hours' => 'intensive-professional-pastry',
            'professional-chef-course-italian-cuisine-325-hours' => 'professional-chef-italian-cuisine',
            'advanced-cooking-techniques-80-hours' => 'advanced-cooking-techniques',
        ];

        return $aliases[$slug] ?? $slug;
    }
}

if (!function_exists('cal_about_ui_string')) {
    function cal_about_ui_string(string $s): string
    {
        return $s;
    }
}

if (!function_exists('cal_language_page_url_by_template')) {
    function cal_language_page_url_by_template(string $template, string $fallback): string
    {
        $lang = cal_current_language();
        $pages = Post::with('meta')
            ->whereHas('meta', fn ($q) => $q->where('meta_key', '_wp_page_template')->where('meta_value', $template))
            ->where('post_type', 'page')
            ->published()
            ->get();

        foreach ($pages as $page) {
            if (\App\Services\PolylangService::getPostLanguage($page->ID) === $lang) {
                return cal_localized_url($page->ID, $lang);
            }
        }

        $page = $pages->first();

        return $page ? cal_localized_url($page->ID, $lang) : cal_localized_path($fallback);
    }
}

if (!function_exists('cal_page_by_slug')) {
    function cal_page_by_slug(string $slug, ?string $lang = null): ?Post
    {
        return \App\Services\PolylangService::findPageBySlug($slug, $lang);
    }
}

if (!function_exists('cal_flag_url')) {
    function cal_flag_url(string $langCode): string
    {
        return \App\Services\FlagService::url($langCode);
    }
}

if (!function_exists('cal_bind_post')) {
    function cal_bind_post(Post $post): void
    {
        request()->attributes->set('_post_id', $post->ID);
        request()->attributes->set('_post', $post);
        AcfFieldService::hydratePostMeta($post);
        \App\Services\PolylangService::warmCachesForPostIds([(int) $post->ID]);
    }
}

if (!function_exists('cal_body_class_for_view')) {
    function cal_body_class_for_view(string $bladeView): string
    {
        $classes = ['no-sidebar', 'full-width-content'];

        $templateClasses = [
            'site.cooking-courses' => ['cal-cooking-courses-template-page', 'page-template-template-cooking-courses'],
            'site.course-enrollment' => ['cal-course-enrollment-template-page', 'page-template-template-course-enrollment'],
            'site.blog' => ['cal-blog-template-page', 'page-template-template-blog'],
            'site.privacy-policy' => ['cal-policy-page', 'page-template-template-privacy-policy'],
            'site.terms-conditions' => ['cal-policy-page', 'page-template-template-terms-conditions'],
            'site.cookies-policy' => ['cal-policy-page', 'page-template-template-cookies-policy'],
            'site.complaints-policy' => ['cal-policy-page', 'page-template-template-complaints-policy'],
            'site.attendance-policy' => ['cal-policy-page', 'page-template-template-attendance-policy'],
        ];

        if (isset($templateClasses[$bladeView])) {
            $classes = array_merge($classes, $templateClasses[$bladeView]);
        }

        return implode(' ', array_unique($classes));
    }
}