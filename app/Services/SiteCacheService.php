<?php

namespace App\Services;

class SiteCacheService
{
    public static function clearMenus(): void
    {
        foreach (['en', 'fr', 'es', 'it', 'zh', 'zh-tw'] as $lang) {
            CalCache::forget("cal.menu.items.{$lang}");
            CalCache::forget("cal.menu.v2.items.{$lang}");
            CalCache::forget("cal.menu.v3.items.{$lang}");
            CalCache::forget("cal.menu.v4.items.{$lang}");
        }
    }

    public static function clearSeo(int $postId): void
    {
        CalCache::forget("cal.seo.post.{$postId}");
    }

    public static function clearSitemap(): void
    {
        CalCache::forget('cal.sitemap.xml');
        CalCache::forget('cal.sitemap.xml.v2-locale-prefix');
    }

    public static function clearCarousel(): void
    {
        foreach (['en', 'fr', 'es', 'it', 'zh', 'zh-tw'] as $lang) {
            CalCache::forget("cal.carousel.html.{$lang}");
        }
    }

    public static function clearFrontPage(): void
    {
        CalCache::forget('cal.frontpage.id');
        foreach (['en', 'fr', 'es', 'it', 'zh', 'zh-tw'] as $lang) {
            CalCache::forget("cal.frontpage.post_id.{$lang}");
            CalCache::forget("cal.frontpage.post_id.v2.{$lang}");
        }
    }

    public static function clearResponseCache(): void
    {
        CalCache::bumpResponseVersion();
    }

    public static function clearAll(): void
    {
        static::clearMenus();
        static::clearSitemap();
        static::clearCarousel();
        static::clearFrontPage();
        static::clearResponseCache();
    }
}
