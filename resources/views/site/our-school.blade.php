@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-our-school-template.css?v=20260717">
@endpush

@section('content')
@php
    $heroTitle = cal_page_field('cal_our_school_hero_title', $page->post_title ?? 'Hospitality Development');
    $heroKicker = cal_page_field('cal_our_school_hero_kicker');
    $heroIntro = cal_page_field('cal_our_school_hero_intro');
    $heroImage = cal_page_field('cal_our_school_hero_image');
    $heroCta = cal_page_field('cal_our_school_hero_cta', []);
    $sections = cal_page_field('cal_our_school_sections', []);
    $ctaTitle = cal_page_field('cal_our_school_cta_title');
    $ctaText = cal_page_field('cal_our_school_cta_text');
    $ctaLink = cal_page_field('cal_our_school_cta_link', []);
    $sections = is_array($sections) ? $sections : [];
    $heroImageUrl = cal_image_url($heroImage);
@endphp

<main id="main" class="site-main cal-our-school-page">
    <section class="cal-our-school-hero">
        <div class="cal-our-school-shell cal-our-school-hero-grid">
            <div class="cal-our-school-hero-copy">
                @if($heroKicker)
                <p class="cal-our-school-kicker">{{ $heroKicker }}</p>
                @endif
                <h1>{{ $heroTitle }}</h1>
                @if($heroIntro)
                <div class="cal-our-school-intro">{!! nl2br(e($heroIntro)) !!}</div>
                @endif
                @if(is_array($heroCta) && !empty($heroCta['url']))
                @php cal_render_link($heroCta, 'cal-our-school-button'); @endphp
                @endif
            </div>
            @if($heroImageUrl)
            <div class="cal-our-school-hero-image">
                <img src="{{ $heroImageUrl }}" alt="{{ $heroTitle }}" loading="lazy">
            </div>
            @endif
        </div>
    </section>

    <section class="cal-our-school-content">
        <div class="cal-our-school-shell">
            @php cal_render_breadcrumbs('cal-our-school-breadcrumbs'); @endphp

            @if(count($sections) > 0)
            <div class="cal-our-school-sections">
                @foreach($sections as $section)
                @php
                    $title = trim((string) ($section['title'] ?? ''));
                    $text = trim((string) ($section['text'] ?? ''));
                    $bullets = is_array($section['bullets'] ?? null) ? $section['bullets'] : [];
                    $imageUrl = cal_image_url($section['image'] ?? '');
                    $layout = $section['layout'] ?? '';
                    $textBlocks = cal_our_school_text_blocks($text);
                    $firstParagraph = $textBlocks['first'];
                    $restParagraphs = $textBlocks['rest'];
                @endphp
                <article class="cal-our-school-section is-{{ $layout }}">
                    <div class="cal-our-school-section-copy">
                        @if($title)
                        <h2>{{ $title }}</h2>
                        @endif
                        @if(count($bullets) > 0)
                            @if($firstParagraph)
                            <div class="cal-our-school-rich-text">{!! cal_our_school_render_paragraph($firstParagraph) !!}</div>
                            @endif
                            <ul class="cal-our-school-bullets">
                                @foreach($bullets as $bullet)
                                @php $bulletText = trim((string) ($bullet['text'] ?? '')); @endphp
                                @if($bulletText)
                                <li>{{ $bulletText }}</li>
                                @endif
                                @endforeach
                            </ul>
                            @foreach($restParagraphs as $paragraph)
                            <div class="cal-our-school-rich-text">{!! cal_our_school_render_paragraph($paragraph) !!}</div>
                            @endforeach
                        @elseif($text)
                            <div class="cal-our-school-rich-text">{!! cal_our_school_render_paragraph($text) !!}</div>
                        @endif
                    </div>
                    @if($imageUrl && $layout !== 'wide')
                    <div class="cal-our-school-section-image">
                        <img src="{{ $imageUrl }}" alt="{{ $title }}" loading="lazy">
                    </div>
                    @endif
                </article>
                @endforeach
            </div>
            @endif

            @if($ctaTitle || $ctaText || (is_array($ctaLink) && !empty($ctaLink['url'])))
            <section class="cal-our-school-cta">
                @if($ctaTitle)
                <h2>{{ $ctaTitle }}</h2>
                @endif
                @if($ctaText)
                <p>{{ $ctaText }}</p>
                @endif
                @if(is_array($ctaLink) && !empty($ctaLink['url']))
                @php cal_render_link($ctaLink, 'cal-our-school-button is-light'); @endphp
                @endif
            </section>
            @endif
        </div>
    </section>
</main>
@endsection