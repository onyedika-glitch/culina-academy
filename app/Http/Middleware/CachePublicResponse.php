<?php

namespace App\Http\Middleware;

use App\Services\CalCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serve fully-rendered HTML from file cache for anonymous GET requests.
 * This is the primary TTFB fix on shared hosting where PHP bootstrap + DB are slow.
 */
class CachePublicResponse
{
    /** @var list<string> */
    protected const SKIP_PATH_SEGMENTS = [
        'admin',
        'api',
        'blog',
        'booking',
        'waitlist',
        'contact-us',
        'course-enrollment',
        'debug-tables',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!$this->shouldCache($request)) {
                return $next($request);
            }

            $cacheKey = CalCache::responseKey($request);
            $cached = CalCache::get($cacheKey);

            if (is_string($cached) && $cached !== '') {
                return response($cached, 200, [
                    'Content-Type' => 'text/html; charset=UTF-8',
                    'Cache-Control' => 'public, s-maxage=7200, max-age=600',
                    'X-Cal-Cache' => 'HIT',
                ]);
            }

            $response = $next($request);

            if ($this->shouldStore($request, $response)) {
                $content = $response->getContent();
                if (is_string($content) && $content !== '') {
                    // 2 hours: cuts TTFB on VPS after first warm; purge via cal:warm-cache / SiteCacheService
                    CalCache::put($cacheKey, $content, now()->addHours(2));
                }
                $response->headers->set('X-Cal-Cache', 'MISS');
                $response->headers->set('Cache-Control', 'public, s-maxage=7200, max-age=600');
            }

            return $response;
        } catch (\Throwable) {
            return $next($request);
        }
    }

    protected function shouldCache(Request $request): bool
    {
        if ($request->method() !== 'GET') {
            return false;
        }

        if (count($request->query()) > 0) {
            return false;
        }

        if ($request->user()) {
            return false;
        }

        $path = rtrim($request->getPathInfo(), '/') ?: '/';

        foreach (self::SKIP_PATH_SEGMENTS as $segment) {
            if (preg_match('#(^|/)' . preg_quote($segment, '#') . '(/|$)#', $path) === 1) {
                return false;
            }
        }

        return true;
    }

    protected function shouldStore(Request $request, Response $response): bool
    {
        if (!$this->shouldCache($request)) {
            return false;
        }

        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');

        return str_contains($contentType, 'text/html') || $contentType === '';
    }
}