<!DOCTYPE html>
<html lang="{{ \App\Services\PolylangService::htmlLang() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $seoMeta = $seoMeta ?? \App\Services\SeoService::forPost(get_the_ID() ?: ($page->ID ?? null));
        $pageTitle = $seoMeta['title'];
        $brandName = 'The Culina Academy';
        // Append brand name unless it already contains it (case-insensitive)
        if (stripos($pageTitle, 'Culina Academy') === false && stripos($pageTitle, 'Culina Academy') === false) {
            $pageTitle .= ' | ' . $brandName;
        }
    @endphp
    <link rel="alternate" type="application/rss+xml" title="{{ $brandName }} &raquo; Feed" href="{{ url('/feed') }}">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $seoMeta['description'] }}">
    @if(!empty($seoMeta['robots']))<meta name="robots" content="{{ $seoMeta['robots'] }}">@endif
    @if(!empty($seoMeta['canonical_url']))<link rel="canonical" href="{{ $seoMeta['canonical_url'] }}">@endif
    @if(!empty($seoMeta['focus_keyword']))<meta name="keywords" content="{{ $seoMeta['focus_keyword'] }}">@endif
    <meta property="og:title" content="{{ $seoMeta['og_title'] }}">
    <meta property="og:description" content="{{ $seoMeta['og_description'] }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="The Culina Academy">
    <meta property="og:locale" content="{{ str_replace('-', '_', \App\Services\PolylangService::htmlLang()) }}">
    @if(!empty($seoMeta['og_image']))<meta property="og:image" content="{{ url($seoMeta['og_image']) }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoMeta['twitter_title'] }}">
    <meta name="twitter:description" content="{{ $seoMeta['twitter_description'] }}">
    @if(!empty($seoMeta['twitter_image']))<meta name="twitter:image" content="{{ url($seoMeta['twitter_image']) }}">@endif

    {{-- Google Search Console verification --}}
    @if(config('services.google.search_console_verification'))
    <meta name="google-site-verification" content="{{ config('services.google.search_console_verification') }}">
    @endif

    {{-- Google Analytics (GA4) — loaded after idle to reduce main-thread blocking --}}
    @if(config('services.google.measurement_id'))
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('consent', 'default', {
        'analytics_storage': 'denied',
        'ad_storage': 'denied',
        'ad_user_data': 'denied',
        'ad_personalization': 'denied',
        'wait_for_update': 500
    });
    (function () {
        var measurementId = @json(config('services.google.measurement_id'));
        function loadGtag() {
            if (window.__calGtagLoaded) return;
            window.__calGtagLoaded = true;
            var script = document.createElement('script');
            script.async = true;
            script.src = 'https://www.googletagmanager.com/gtag/js?id=' + measurementId;
            document.head.appendChild(script);
            gtag('js', new Date());
            gtag('config', measurementId, {
                'anonymize_ip': true,
                'cookie_flags': 'SameSite=None;Secure',
                'cookie_prefix': 'ga_',
                'cookie_domain': @json(request()->getHost()),
                'page_title': @json($pageTitle),
                'page_path': @json(request()->getPathInfo())
            });
        }
        if ('requestIdleCallback' in window) {
            requestIdleCallback(loadGtag, { timeout: 3000 });
        } else {
            window.addEventListener('load', loadGtag, { once: true });
        }
    })();
    </script>
    @endif

    {{-- Hreflang tags for multi-language SEO --}}
    @php
        $hreflangLinks = \App\Services\PolylangService::getLanguageSwitcherLinks();
        $currentPath = rtrim(request()->getPathInfo(), '/') ?: '/';
    @endphp
    @foreach($hreflangLinks as $hl)
        @php
            // Skip locale-homepage fallbacks for non-homepage pages (no equivalent translation exists)
            $altPath = rtrim(parse_url($hl['url'], PHP_URL_PATH) ?: '/', '/') ?: '/';
            $isHomepage = $altPath === '/' . $hl['slug'] || $altPath === '/' . $hl['slug'] . '/home';
            $currentIsHomepage = $currentPath === '/' . $hl['slug'] || $currentPath === '/' . $hl['slug'] . '/home';
        @endphp
        @if(!$isHomepage || $currentIsHomepage)
    <link rel="alternate" hreflang="{{ $hl['slug'] }}" href="{{ url($hl['url']) }}">
        @endif
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url('/en') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @php
        $gf = 'https://fonts.googleapis.com/css2?';
        if (View::hasSection('minimal-fonts')) {
            $gf .= 'family=Montserrat:wght@600;700;800&family=Playfair+Display:wght@700;800&';
        } else {
            $gf .= 'family=Cormorant+Garamond:wght@400;600;700&';
            $gf .= 'family=Montserrat:wght@400;500;600;700;800;900&';
            $gf .= 'family=Outfit:wght@300;400;600&';
            $gf .= 'family=Playfair+Display:ital,wght@0,700;0,800;0,900;1,700&';
            $gf .= 'family=Roboto:wght@400;500;700&';
            $gf .= 'family=Roboto+Slab:wght@600;700&';
        }
        $gf .= 'display=optional';
    @endphp
    <link rel="preload" as="style" href="{{ $gf }}">
    <link rel="stylesheet" href="{{ $gf }}" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="{{ $gf }}"></noscript>

    @stack('head')

    @php $assetVer = '20260717c'; @endphp
    @hasSection('critical-css')
        @yield('critical-css')
    @else
    <style>
        /* ── Default critical CSS (all pages) ── */
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html{scroll-behavior:smooth;scroll-padding-top:127px}
        body{font-family:-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif;font-weight:400;font-size:17px;line-height:1.5;color:#1f2937;background:#fff;padding-top:127px}
        .crisp-nav-topbar{background:#1a1a1a;color:#fff;padding:8px 0;font-size:14px;position:fixed;top:0;left:0;width:100%;z-index:1002}
        .crisp-nav-topbar-container{max-width:1440px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;padding:0 20px}
        .crisp-nav-header{background:#fff;position:fixed;top:37px;left:0;width:100%;z-index:1001;box-shadow:0 2px 8px rgba(0,0,0,.08)}
        .crisp-nav-container{max-width:1440px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;padding:15px 20px;min-height:70px}
        @media(max-width:1024px){html{scroll-padding-top:153px}body{padding-top:153px}.crisp-nav-topbar{display:none}.crisp-nav-header{top:74px}}
        .crisp-nav-logo img{max-height:50px;width:auto;display:block}
        img{max-width:100%;height:auto}
        a{color:inherit;text-decoration:none}
    </style>
    @endif
    @unless(View::hasSection('lean-assets'))
    <link rel="stylesheet" href="/css/all.min.css?v={{ $assetVer }}" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="/css/main.min.css?v={{ $assetVer }}" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="/css/style.min.css?v={{ $assetVer }}" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="/css/mobile.min.css?v={{ $assetVer }}" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="/css/unsemantic-grid.min.css?v={{ $assetVer }}" media="print" onload="this.media='all'">
    @endunless
    <link rel="stylesheet" href="/css/cal-custom-header-footer.css?v={{ $assetVer }}" media="print" onload="this.media='all'">
    <noscript>
        @unless(View::hasSection('lean-assets'))
        <link rel="stylesheet" href="/css/all.min.css?v={{ $assetVer }}">
        <link rel="stylesheet" href="/css/main.min.css?v={{ $assetVer }}">
        <link rel="stylesheet" href="/css/style.min.css?v={{ $assetVer }}">
        <link rel="stylesheet" href="/css/mobile.min.css?v={{ $assetVer }}">
        <link rel="stylesheet" href="/css/unsemantic-grid.min.css?v={{ $assetVer }}">
        @endunless
        <link rel="stylesheet" href="/css/cal-custom-header-footer.css?v={{ $assetVer }}">
    </noscript>
    @stack('styles')
</head>
<body class="{{ $bodyClass ?? 'no-sidebar full-width-content' }}">
    @include('partials.site-header')

    <div id="page" class="site grid-container container hfeed">
        <div id="content" class="site-content">
            @yield('content')
        </div>
    </div>

    @include('partials.site-footer')

    @include('partials.cookie-consent')

    @hasSection('defer-header-js')
    <script>
    (function () {
        function loadHeaderJs() {
            if (document.querySelector('script[data-cal-src="header-js"]')) return;
            var s = document.createElement('script');
            s.src = '/js/cal-custom-header-footer.js?v={{ $assetVer }}';
            s.defer = true;
            s.setAttribute('data-cal-src', 'header-js');
            document.body.appendChild(s);
        }
        if ('requestIdleCallback' in window) {
            requestIdleCallback(loadHeaderJs, { timeout: 2500 });
        } else {
            window.addEventListener('load', loadHeaderJs, { once: true });
        }
    })();
    </script>
    @else
    <script src="/js/cal-custom-header-footer.js?v={{ $assetVer }}" defer></script>
    @endif
    <script>
    (function () {
        var detailUrls = {
            introduction: @json(\App\Services\OnlineCourseService::detailPath(\App\Services\OnlineCourseService::INTRODUCTION_SLUG)),
            michelin: @json(\App\Services\OnlineCourseService::detailPath(\App\Services\OnlineCourseService::MICHELIN_SLUG)),
            listing: @json(\App\Services\OnlineCourseService::listingPath())
        };

        function localOnlineCourseUrl(href) {
            var path = (href || '').toLowerCase();
            if (path.indexOf('introduction-to-professional-chef') !== -1 || path.indexOf('introduction-to-the-professional-chef') !== -1) {
                return detailUrls.introduction;
            }
            if (path.indexOf('michelin-star-advanced-techniques') !== -1) {
                return detailUrls.michelin;
            }
            if (path.indexOf('culinaacademyonline.com') !== -1) {
                return detailUrls.listing;
            }
            return null;
        }

        function rewriteOnlineLinks(root) {
            (root || document).querySelectorAll('a[href*="culinaacademyonline.com"]').forEach(function (link) {
                var local = localOnlineCourseUrl(link.getAttribute('href'));
                if (local) {
                    link.setAttribute('href', local);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            rewriteOnlineLinks();
        });
    })();
    </script>
    @stack('scripts')

    {{-- JSON-LD Structured Data --}}
    @php
        $schemaPhone = trim((string) cal_option('cal_header_phone', '+1 555 0100'));
        $organizationLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'The Culina Academy',
            'url' => url(cal_localized_path('/')),
            'logo' => url('/uploads/2026/05/cropped-logob-180x180.png'),
            'description' => 'The Culina Academy offers professional chef courses, culinary training, and hospitality programmes.',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '45 Example Street, 3rd Floor',
                'addressLocality' => 'Your City',
                'addressRegion' => 'England',
                'postalCode' => '00000',
                'addressCountry' => 'GB',
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => $schemaPhone,
                'contactType' => 'customer service',
                'availableLanguage' => ['English', 'French', 'Spanish', 'Italian', 'Chinese'],
            ],
            'sameAs' => [
                'https://www.facebook.com/example',
                'https://www.instagram.com/example',
                'https://www.linkedin.com/company/example',
                'https://x.com/example',
            ],
        ];
        $websiteLd = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'The Culina Academy',
            'url' => url(cal_localized_path('/')),
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($organizationLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    <script type="application/ld+json">{!! json_encode($websiteLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @stack('json-ld')
</body>
</html>