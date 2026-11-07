<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
    @php
        $locales = ['en', 'fr', 'es', 'it', 'zh', 'zh-tw'];
        $baseUrl = url('/');
    @endphp

    {{-- Homepage (English default at /en) --}}
    <url>
        <loc>{{ $baseUrl }}/en</loc>
        <priority>1.0</priority>
        <changefreq>weekly</changefreq>
        @foreach($locales as $lang)
            @php $href = $baseUrl . '/' . $lang; @endphp
            <xhtml:link rel="alternate" hreflang="{{ $lang }}" href="{{ $href }}"/>
        @endforeach
        <xhtml:link rel="alternate" hreflang="x-default" href="{{ $baseUrl }}/en"/>
    </url>

    {{-- Static pages --}}
    @foreach($pages as $page)
        @php
            $pageLang = \App\Services\PolylangService::getPostLanguage($page->ID);
            $pageUrl = $baseUrl . \App\Services\PolylangService::buildPostUrl($page->ID, $pageLang);
            $translationGroup = \App\Services\PolylangService::getTranslationGroup($page->ID);
        @endphp
        <url>
            <loc>{{ $pageUrl }}</loc>
            <priority>{{ $page->post_name === 'home' ? '1.0' : '0.8' }}</priority>
            <changefreq>monthly</changefreq>
            @foreach($locales as $lang)
                @php
                    $altId = $translationGroup[$lang] ?? 0;
                    $altUrl = $altId ? $baseUrl . \App\Services\PolylangService::buildPostUrl($altId, $lang) : '';
                @endphp
                @if($altUrl)
                    <xhtml:link rel="alternate" hreflang="{{ $lang }}" href="{{ $altUrl }}"/>
                @endif
            @endforeach
            @if(!isset($translationGroup['en']))
                <xhtml:link rel="alternate" hreflang="x-default" href="{{ $pageUrl }}"/>
            @endif
        </url>
    @endforeach

    {{-- Course pages --}}
    @foreach($courses as $course)
        @php
            $courseLang = \App\Services\PolylangService::getPostLanguage($course->ID);
            $courseUrl = $baseUrl . \App\Services\PolylangService::buildPostUrl($course->ID, $courseLang);
            $courseTransGroup = \App\Services\PolylangService::getTranslationGroup($course->ID);
        @endphp
        <url>
            <loc>{{ $courseUrl }}</loc>
            <priority>0.9</priority>
            <changefreq>monthly</changefreq>
            @foreach($locales as $lang)
                @php
                    $altId = $courseTransGroup[$lang] ?? 0;
                    $altUrl = $altId ? $baseUrl . \App\Services\PolylangService::buildPostUrl($altId, $lang) : '';
                @endphp
                @if($altUrl)
                    <xhtml:link rel="alternate" hreflang="{{ $lang }}" href="{{ $altUrl }}"/>
                @endif
            @endforeach
        </url>
    @endforeach

    {{-- Blog posts --}}
    @foreach($posts as $post)
        @php
            $postLang = \App\Services\PolylangService::getPostLanguage($post->ID);
            $postUrl = $baseUrl . \App\Services\PolylangService::buildPostUrl($post->ID, $postLang);
            $postTransGroup = \App\Services\PolylangService::getTranslationGroup($post->ID);
        @endphp
        <url>
            <loc>{{ $postUrl }}</loc>
            <priority>0.6</priority>
            <changefreq>monthly</changefreq>
            @foreach($locales as $lang)
                @php
                    $altId = $postTransGroup[$lang] ?? 0;
                    $altUrl = $altId ? $baseUrl . \App\Services\PolylangService::buildPostUrl($altId, $lang) : '';
                @endphp
                @if($altUrl)
                    <xhtml:link rel="alternate" hreflang="{{ $lang }}" href="{{ $altUrl }}"/>
                @endif
            @endforeach
        </url>
    @endforeach
</urlset>
