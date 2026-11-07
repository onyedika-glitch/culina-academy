@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-faq-template.css?v=20260706">
@endpush

@push('json-ld')
@php
    $faqCategories = cal_page_field('cal_faq_categories', []);
    $faqQuestions = [];
    if (is_array($faqCategories)) {
        foreach ($faqCategories as $category) {
            $questions = is_array($category['questions'] ?? null) ? $category['questions'] : [];
            foreach ($questions as $q) {
                $qTitle = trim((string) ($q['question'] ?? ''));
                $answer = trim(strip_tags((string) ($q['answer'] ?? '')));
                if ($qTitle && $answer) {
                    $faqQuestions[] = [
                        '@type' => 'Question',
                        'name' => $qTitle,
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $answer,
                        ],
                    ];
                }
            }
        }
    }
@endphp
@if(count($faqQuestions) > 0)
@php
    $faqLd = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqQuestions,
    ];
@endphp
<script type="application/ld+json">{!! json_encode($faqLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endif
@endpush

@section('content')
@php
    $heroTitle = cal_page_field('cal_faq_hero_title', $page->post_title ?? 'FAQs');
    $heading = cal_page_field('cal_faq_heading');
    $intro = cal_page_field('cal_faq_intro');
    $categories = cal_page_field('cal_faq_categories', []);
    $ctaHeading = cal_page_field('cal_faq_cta_heading');
    $ctaText = cal_page_field('cal_faq_cta_text');
    $ctaPrimaryLink = cal_page_field('cal_faq_cta_primary_link', []);
    $ctaSecondaryLink = cal_page_field('cal_faq_cta_secondary_link', []);
    $categories = is_array($categories) ? $categories : [];
    $ctaPrimaryLink = is_array($ctaPrimaryLink) ? $ctaPrimaryLink : [];
    $ctaSecondaryLink = is_array($ctaSecondaryLink) ? $ctaSecondaryLink : [];
@endphp

<main id="main" class="site-main cal-faq-page-template">
    @php cal_render_page_banner('cal-faq', $heroTitle); @endphp

    <section class="cal-faq-content">
        <div class="cal-faq-shell">
            @php cal_render_breadcrumbs('cal-faq-breadcrumbs'); @endphp

            <div class="cal-faq-page" id="culina-academy-faqs">
                @if($heading)
                <h2>{{ $heading }}</h2>
                @endif

                @if($intro)
                <div class="cal-faq-intro">{!! nl2br(e($intro)) !!}</div>
                @endif

                @if(count($categories) > 0)
                <div class="cal-faq-categories">
                    @foreach($categories as $index => $category)
                    @php
                        $categoryTitle = trim((string) ($category['title'] ?? ''));
                        $questions = is_array($category['questions'] ?? null) ? $category['questions'] : [];
                        // Guard: long body copy must never render as a bold category heading.
                        if ($categoryTitle !== '' && mb_strlen($categoryTitle) > 90) {
                            $categoryTitle = mb_substr($categoryTitle, 0, 70) . '…';
                        }
                    @endphp
                    @if($categoryTitle || count($questions) > 0)
                    <details class="cal-category"{{ $index === 0 ? ' open' : '' }}>
                        @if($categoryTitle)
                        <summary><h2>{{ $categoryTitle }}</h2></summary>
                        @endif
                        @if(count($questions) > 0)
                        <div class="cal-category-body">
                            @foreach($questions as $question)
                            @php
                                $questionTitle = trim((string) ($question['question'] ?? ''));
                                $answer = $question['answer'] ?? '';
                            @endphp
                            @if($questionTitle || $answer)
                            <details class="cal-question">
                                @if($questionTitle)
                                <summary><h3>{{ $questionTitle }}</h3></summary>
                                @endif
                                @if($answer)
                                <div class="cal-faq-answer">{!! \App\Services\CulinaAcademy::fixContentUrls($answer) !!}</div>
                                @endif
                            </details>
                            @endif
                            @endforeach
                        </div>
                        @endif
                    </details>
                    @endif
                    @endforeach
                </div>
                @elseif(trim($page->post_content ?? ''))
                <div class="cal-faq-entry">
                    {!! \App\Services\CulinaAcademy::theContent() !!}
                </div>
                @endif

                @if($ctaHeading || $ctaText || !empty($ctaPrimaryLink['url']) || !empty($ctaSecondaryLink['url']))
                <section class="cal-faq-cta">
                    @if($ctaHeading)
                    <h2>{{ $ctaHeading }}</h2>
                    @endif
                    @if($ctaText)
                    {!! \App\Services\CulinaAcademy::fixContentUrls($ctaText) !!}
                    @endif
                    @if(!empty($ctaPrimaryLink['url']) || !empty($ctaSecondaryLink['url']))
                    <div class="cal-faq-actions">
                        @if(!empty($ctaPrimaryLink['url']) && !empty($ctaPrimaryLink['title']))
                        <a class="cal-button" href="{{ cal_migrate_url($ctaPrimaryLink['url']) }}"@if(!empty($ctaPrimaryLink['target'])) target="{{ $ctaPrimaryLink['target'] }}" rel="noopener"@endif>{{ $ctaPrimaryLink['title'] }}</a>
                        @endif
                        @if(!empty($ctaSecondaryLink['url']) && !empty($ctaSecondaryLink['title']))
                        <a class="cal-button secondary" href="{{ cal_migrate_url($ctaSecondaryLink['url']) }}"@if(!empty($ctaSecondaryLink['target'])) target="{{ $ctaSecondaryLink['target'] }}" rel="noopener"@endif>{{ $ctaSecondaryLink['title'] }}</a>
                        @endif
                    </div>
                    @endif
                </section>
                @endif
            </div>
        </div>
    </section>
</main>
@endsection