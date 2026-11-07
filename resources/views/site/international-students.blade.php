@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-international-students-template.css?v=20260712">
@endpush

@section('content')
@php
    $heroTitle = cal_page_field('cal_international_students_hero_title', $page->post_title ?? 'International Students');
    $logo = cal_page_field('cal_international_students_logo');
    $logoUrl = cal_image_url($logo);
    $logoAlt = is_array($logo) ? ($logo['alt'] ?? $heroTitle) : $heroTitle;
    $introHeading = cal_page_field('cal_international_students_intro_heading');
    $introHighlight = cal_page_field('cal_international_students_intro_highlight');
    $introParagraphs = cal_page_field('cal_international_students_intro_paragraphs', []);
    $sections = cal_page_field('cal_international_students_sections', []);
    $ctaLabel = cal_page_field('cal_international_students_cta_label');
    $ctaUrl = cal_migrate_url((string) cal_page_field('cal_international_students_cta_url', ''));
    $introParagraphs = is_array($introParagraphs) ? $introParagraphs : [];
    $sections = is_array($sections) ? $sections : [];
@endphp

<main id="main" class="site-main cal-international-students-page">
    @php cal_render_page_banner('cal-is', $heroTitle); @endphp

    <section class="cal-is-content">
        <div class="cal-is-shell">
            @php cal_render_breadcrumbs('cal-is-breadcrumbs'); @endphp

            @if($logoUrl)
            <div class="cal-is-logo">
                <img src="{{ $logoUrl }}" alt="{{ $logoAlt }}" loading="lazy">
            </div>
            @endif

            @if($introHeading || count($introParagraphs) > 0)
            <section class="cal-is-intro">
                @if($introHeading)
                <h2>{{ $introHeading }}</h2>
                @endif

                @foreach($introParagraphs as $index => $row)
                @php $paragraph = trim((string) ($row['paragraph'] ?? '')); @endphp
                @if($paragraph !== '')
                <p>
                    @if($index === 0 && $introHighlight)
                    <span class="cal-is-highlight">{{ $introHighlight }}</span>
                    @endif
                    {{ $paragraph }}
                </p>
                @endif
                @endforeach
            </section>
            @endif

            @if(count($sections) > 0)
            @foreach($sections as $section)
            @php
                $heading = trim((string) ($section['heading'] ?? ''));
                $paragraphs = is_array($section['paragraphs'] ?? null) ? $section['paragraphs'] : [];
                $bullets = is_array($section['bullets'] ?? null) ? $section['bullets'] : [];
                $bulletItems = [];
                foreach ($bullets as $bulletRow) {
                    $bullet = trim((string) ($bulletRow['bullet'] ?? ''));
                    if ($bullet !== '') {
                        $bulletItems[] = $bullet;
                    }
                }
            @endphp
            <section class="cal-is-section">
                @if($heading)
                <h2>{{ $heading }}</h2>
                @endif

                @foreach($paragraphs as $paragraphRow)
                @php $paragraph = trim((string) ($paragraphRow['paragraph'] ?? '')); @endphp
                @if($paragraph !== '')
                <p>{{ $paragraph }}</p>
                @endif
                @endforeach

                @if(count($bulletItems) > 0)
                <ul>
                    @foreach($bulletItems as $bullet)
                    <li>{{ $bullet }}</li>
                    @endforeach
                </ul>
                @endif
            </section>
            @endforeach
            @endif

            @if($ctaLabel && $ctaUrl)
            <p class="cal-is-cta">
                <a href="{{ $ctaUrl }}" class="cal-is-button">{{ $ctaLabel }}</a>
            </p>
            @endif
        </div>
    </section>
</main>
@endsection