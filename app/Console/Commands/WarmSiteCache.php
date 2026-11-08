<?php

namespace App\Console\Commands;

use App\Services\CarouselService;
use App\Services\MenuService;
use App\Services\PolylangService;
use App\Services\SeoService;
use App\Services\SiteCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class WarmSiteCache extends Command
{
    protected $signature = 'cal:warm-cache {--url= : Base site URL to warm response cache}';

    protected $description = 'Pre-warm file caches (menus, carousel, homepage HTML) after deploy';

    public function handle(): int
    {
        SiteCacheService::clearResponseCache();

        foreach (['en', 'fr', 'es', 'it', 'zh', 'zh-tw'] as $lang) {
            PolylangService::setCurrentLanguage($lang);
            MenuService::getPrimaryMenuItems();
            CarouselService::render();

            $frontPage = PolylangService::getFrontPage($lang);
            if ($frontPage) {
                SeoService::forPost($frontPage->ID);
            }
        }

        $baseUrl = rtrim((string) ($this->option('url') ?: config('app.url')), '/');
        $paths = [
            '/',
            '/about-us',
            '/faqs',
            '/cooking-courses',
            '/online-courses',
            '/careers',
            '/how-to-apply',
            '/restaurant-partners',
            '/international-students',
            '/reviews',
            '/contact-us',
            '/terms-and-conditions',
            '/it',
            '/it/cooking-courses',
            '/it/about-us',
            '/fr',
            '/fr/cooking-courses',
            '/es',
            '/es/cooking-courses',
            '/zh',
            '/zh-tw',
        ];

        foreach ($paths as $path) {
            $url = $baseUrl.$path;
            try {
                $response = Http::timeout(90)
                    ->withHeaders([
                        'Accept' => 'text/html,application/xhtml+xml,image/webp,*/*',
                        'User-Agent' => 'CAL-Cache-Warmer/1.0',
                    ])
                    ->get($url);
                $cache = $response->header('X-Cal-Cache') ?: 'n/a';
                $this->line(sprintf('%s → %d (%s)', $url, $response->status(), $cache));
                if ($response->failed()) {
                    $body = trim(strip_tags((string) $response->body()));
                    if ($body !== '') {
                        $this->warn(substr($body, 0, 200));
                    }
                }
            } catch (\Throwable $e) {
                $this->warn("Failed to warm {$url}: {$e->getMessage()}");
            }
        }

        $this->info('Site cache warm-up complete.');

        return self::SUCCESS;
    }
}
