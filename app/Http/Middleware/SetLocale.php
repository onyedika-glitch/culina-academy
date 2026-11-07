<?php

namespace App\Http\Middleware;

use App\Services\PolylangService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');
        $code = $locale ? PolylangService::normalizeCode($locale) : 'en';

        PolylangService::setCurrentLanguage($code);
        $request->attributes->set('cal_language', $code);

        $language = PolylangService::getLanguage($code);
        app()->setLocale(strtolower(str_replace('-', '_', ($language['locale'] ?? 'en_GB'))));

        return $next($request);
    }
}