<?php

namespace App\Services;

use App\Models\Post;

class CulinaAcademy
{
    public static function renderBreadcrumbs(string $cssClass = '', array $items = [], string $current = ''): void
    {
        $current = $current ?: static::getTheTitle();
        $locale = PolylangService::currentLanguage();
        $baseUrl = url('/'.$locale);

        // Build JSON-LD BreadcrumbList
        $breadcrumbItems = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => cal_i18n_text('Home'), 'item' => $baseUrl],
        ];
        $position = 2;
        foreach ($items as $item) {
            if (empty($item['url']) || empty($item['title'])) {
                continue;
            }
            $breadcrumbItems[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $item['title'],
                'item' => url('/'.$locale.'/'.ltrim(AcfFieldService::migrateUrl($item['url']), '/')),
            ];
        }
        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $current,
        ];

        echo '<nav class="'.esc_attr($cssClass).' cal-page-breadcrumbs" aria-label="Breadcrumb">';
        echo '<a href="'.PolylangService::prefixForLanguage().'">'.esc_html(cal_i18n_text('Home')).'</a>';
        foreach ($items as $item) {
            if (empty($item['url']) || empty($item['title'])) {
                continue;
            }
            $url = AcfFieldService::migrateUrl($item['url']);
            echo '<span class="cal-breadcrumb-sep">&raquo;</span>';
            echo '<a href="'.esc_url($url).'">'.esc_html($item['title']).'</a>';
        }
        echo '<span class="cal-breadcrumb-sep">&raquo;</span>';
        echo '<span class="cal-breadcrumb-current">'.esc_html($current).'</span>';
        echo '</nav>';

        // Emit JSON-LD BreadcrumbList
        echo '<script type="application/ld+json">'.json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbItems,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).'</script>';
    }

    public static function renderPageBanner(string $cssClass, string $title): void
    {
        echo '<section class="'.esc_attr($cssClass).'-hero cal-page-hero">';
        echo '<div class="'.esc_attr($cssClass).'-hero-inner cal-page-shell">';
        echo '<h1>'.esc_html($title).'</h1>';
        echo '</div></section>';
    }

    public static function renderLink(mixed $link, string $class = ''): void
    {
        if (empty($link) || ! is_array($link) || empty($link['url'])) {
            return;
        }

        $url = AcfFieldService::migrateUrl($link['url']);
        $title = ! empty($link['title']) ? $link['title'] : $url;
        $target = ! empty($link['target']) ? $link['target'] : '';
        $classAttr = $class ? ' class="'.esc_attr($class).'"' : '';
        $targetAttr = $target ? ' target="'.esc_attr($target).'" rel="noopener"' : '';

        echo '<a href="'.esc_url($url).'"'.$classAttr.$targetAttr.'>'.esc_html($title).'</a>';
    }

    public static function formatPrice(mixed $courseIdOrRaw): string
    {
        if (is_numeric($courseIdOrRaw)) {
            $raw = AcfFieldService::getField((int) $courseIdOrRaw, 'ca_price');
        } else {
            $raw = $courseIdOrRaw;
        }
        if (empty($raw)) {
            return '';
        }
        $number = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '', (string) $raw));
        if ($number <= 0) {
            return (string) $raw;
        }

        return '£'.number_format($number);
    }

    public static function formatCourseDuration(mixed $hours, mixed $duration, ?string $lang = null): string
    {
        $lang = PolylangService::normalizeCode($lang ?? PolylangService::currentLanguage());
        $h = trim((string) $hours);
        $d = CourseDisplayService::duration(trim((string) $duration), $lang);
        $units = ['en' => 'hours', 'fr' => 'heures', 'es' => 'horas', 'it' => 'ore', 'zh' => '小时', 'zh-tw' => '小時'];
        $unit = $units[$lang] ?? $units['en'];
        if ($h !== '' && $d !== '') {
            return $h.' '.$unit.' / '.$d;
        }
        if ($h !== '') {
            return $h.' '.$unit;
        }

        return $d;
    }

    public static function getTheID(): int
    {
        return (int) request()->attributes->get('_post_id', 0);
    }

    public static function getTheTitle(): string
    {
        $id = static::getTheID();
        if (! $id) {
            return '';
        }
        $post = Post::find($id);

        return $post ? $post->post_title : '';
    }

    public static function theContent(): string
    {
        $id = static::getTheID();
        if (! $id) {
            return '';
        }
        $post = Post::find($id);
        if (! $post) {
            return '';
        }

        return static::fixContentUrls($post->post_content);
    }

    public static function fixContentUrls(string $content): string
    {
        $content = preg_replace_callback(
            '/\b(href|src)=(["\'])(.*?)\2/i',
            static function ($matches) {
                $url = html_entity_decode($matches[3], ENT_QUOTES, 'UTF-8');
                $migrated = AcfFieldService::migrateUrl($url);

                return $matches[1].'='.$matches[2].esc_url($migrated).$matches[2];
            },
            $content
        ) ?? $content;

        $content = str_replace('/wp-content/uploads/', '/uploads/', $content);

        return $content;
    }

    /**
     * Build VideoObject JSON-LD for YouTube embeds so Google can index the video.
     */
    public static function videoJsonLd(string $videoUrl, string $pageTitle): string
    {
        $embed = cal_youtube_embed_url($videoUrl);
        if ($embed === '') {
            return '';
        }

        // Extract YouTube ID from embed URL
        if (! preg_match('#youtube\\.com/embed/([a-zA-Z0-9_-]{11})#', $embed, $m)) {
            return '';
        }

        $videoId = $m[1];
        $watchUrl = 'https://www.youtube.com/@example'.$videoId;
        $thumbUrl = 'https://i.ytimg.com/vi/'.$videoId.'/hqdefault.jpg';

        $json = [
            '@context' => 'https://schema.org',
            '@type' => 'VideoObject',
            'name' => $pageTitle.' — Video',
            'description' => strip_tags((string) $pageTitle),
            'thumbnailUrl' => $thumbUrl,
            'embedUrl' => $embed,
            'contentUrl' => $watchUrl,
            'uploadDate' => date('Y-m-d'),
        ];

        return '<script type="application/ld+json">'.json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).'</script>';
    }
}
