@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-careers-template.css?v=20260706">
@endpush

@section('content')
@php
    use App\Services\UiTranslationService;

    $heroTitle = UiTranslationService::translate((string) cal_page_field('cal_careers_hero_title', $page->post_title ?? 'Careers'));
    $introHeading = UiTranslationService::translate((string) cal_page_field('cal_careers_intro_heading'));
    $introText = UiTranslationService::translate((string) cal_page_field('cal_careers_intro_text'));
    $featuresHeading = UiTranslationService::translate((string) cal_page_field('cal_careers_features_heading'));
    $featuresText = UiTranslationService::translate((string) cal_page_field('cal_careers_features_text'));
    $cards = cal_page_field('cal_careers_cards', []);
    $cards = is_array($cards) ? $cards : [];
@endphp

<main id="main" class="site-main cal-careers-page">
    @php cal_render_page_banner('cal-careers', $heroTitle); @endphp

    <section class="cal-careers-content">
        <div class="cal-careers-shell">
            @php cal_render_breadcrumbs('cal-careers-breadcrumbs'); @endphp

            @if($introHeading || $introText)
            <section class="cal-careers-intro">
                @if($introHeading)
                <h2>{{ $introHeading }}</h2>
                @endif
                @if($introText)
                <p>{{ $introText }}</p>
                @endif
            </section>
            @endif
        </div>
    </section>

    @if($featuresHeading || $featuresText || count($cards) > 0)
    <section class="cal-careers-features">
        <div class="cal-careers-shell">
            @if($featuresHeading || $featuresText)
            <header class="cal-careers-features-header">
                @if($featuresHeading)
                <h2>{{ $featuresHeading }}</h2>
                @endif
                @if($featuresText)
                <p>{{ $featuresText }}</p>
                @endif
            </header>
            @endif

            @if(count($cards) > 0)
            <div class="cal-careers-grid">
                @foreach($cards as $card)
                @php
                    $imageUrl = cal_image_url($card['image'] ?? '');
                    $imageAlt = is_array($card['image'] ?? null) ? ($card['image']['alt'] ?? '') : '';
                    $title = UiTranslationService::translate(trim((string) ($card['title'] ?? '')));
                    $text = UiTranslationService::translate(trim((string) ($card['text'] ?? '')));
                @endphp
                <article class="cal-careers-card">
                    <div class="cal-careers-card-media{{ $imageUrl ? '' : ' is-empty' }}">
                        @if($imageUrl)
                        <img src="{{ $imageUrl }}" alt="{{ $imageAlt ?: $title }}" loading="lazy">
                        @endif
                    </div>
                    @if($title)
                    <h3>{{ $title }}</h3>
                    @endif
                    @if($text)
                    <p>{{ $text }}</p>
                    @endif
                </article>
                @endforeach
            </div>
            @endif
        </div>
    </section>
    @endif
</main>
@endsection