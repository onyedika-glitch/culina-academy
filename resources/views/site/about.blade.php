@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="/css/cal-about-template.css?v=20260710">
<link rel="stylesheet" href="/css/cal-reviews-template.css?v=20260710">
<style>
.cal-video-modal{position:fixed;top:0;left:0;right:0;bottom:0;z-index:999999;display:none;align-items:center;justify-content:center;padding:20px}
.cal-video-modal:not([hidden]){display:flex}
.cal-video-modal-backdrop{position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.85);cursor:pointer}
.cal-video-modal-content{position:relative;width:100%;max-width:900px;background:#000;border-radius:8px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.5)}
.cal-video-modal-close{position:absolute;top:10px;right:10px;z-index:2;width:36px;height:36px;border:none;background:rgba(0,0,0,.6);color:#fff;font-size:22px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1}
.cal-video-modal-close:hover{background:rgba(0,0,0,.8)}
.cal-video-modal-embed{position:relative;padding-bottom:56.25%;height:0}
.cal-video-modal-embed iframe{position:absolute;top:0;left:0;width:100%;height:100%;border:0}
.cal-about-video-trigger{position:relative;cursor:pointer;border:none;padding:0;background:none}
.cal-about-play-icon{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:56px;height:56px;background:rgba(212,160,23,.9);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:22px;padding-left:4px;box-shadow:0 4px 15px rgba(0,0,0,.3);transition:transform .2s}
.cal-about-video-trigger:hover .cal-about-play-icon{transform:translate(-50%,-50%) scale(1.1)}
</style>
@push('json-ld')
@if(isset($videoJsonLd) && $videoJsonLd)
{!! $videoJsonLd !!}
@endif
@endpush
@endpush
@section('content')
@php
    $bannerTitle = $page->post_title ?? 'About Us';
    $heroTitle = cal_about_field('cal_about_hero_title');
    $heroSubtitle = cal_about_field('cal_about_hero_subtitle');
    $whyHeading = cal_about_field('cal_about_why_heading');
    $whyText = cal_about_field('cal_about_why_text');
    $storyHeading = cal_about_field('cal_about_story_heading');
    $storyParagraphs = (array) (cal_about_field('cal_about_story_paragraphs') ?: []);
    $storyVideo = cal_oembed_html(cal_about_field('cal_about_story_video'));
    $storyVideoRaw = trim((string) cal_about_field('cal_about_story_video'));
    $ctaHeading = cal_about_field('cal_about_cta_heading');
    $ctaBtnText = cal_about_field('cal_about_cta_button_text');
    $ctaBtnUrl = cal_about_field('cal_about_cta_button_url');
    $reviewsHeading = cal_about_field('cal_about_reviews_heading');
    $reviews = (array) (cal_about_field('cal_about_reviews') ?: []);
    $reviewsBtnText = cal_about_field('cal_about_reviews_button_text');
    $reviewsBtnUrl = cal_about_field('cal_about_reviews_button_url') ?: '/reviews';
    $videoJsonLd = $storyVideoRaw ? \App\Services\CulinaAcademy::videoJsonLd($storyVideoRaw, $bannerTitle) : '';
@endphp
<main id="main" class="site-main cal-about-page">
    @php cal_render_page_banner('cal-about', $bannerTitle); @endphp

    <section class="cal-about-breadcrumb-bar">
        <div class="cal-about-shell">
            @php cal_render_breadcrumbs('cal-about-breadcrumbs'); @endphp

            @if($heroTitle || $heroSubtitle)
            <div class="cal-about-hero-inner">
                @if($heroTitle)<h2 class="cal-about-hero-title">{{ $heroTitle }}</h2>@endif
                @if($heroSubtitle)<p class="cal-about-hero-sub">{{ $heroSubtitle }}</p>@endif
            </div>
            @endif
        </div>
    </section>

    @if($whyHeading || $whyText)
    <section class="cal-about-why">
        <div class="cal-about-shell">
            <div class="cal-about-why-header">
                @if($whyHeading)<h2>{{ $whyHeading }}</h2>@endif
                @if($whyText)<p>{{ $whyText }}</p>@endif
            </div>
        </div>
    </section>
    @endif

    <section class="cal-about-info">
        <div class="cal-about-shell">
            <div class="cal-about-info-grid">
                @include('partials.about-info-card', ['iconKey' => 'cal_about_philosophy_icon', 'headingKey' => 'cal_about_philosophy_heading', 'textKey' => 'cal_about_philosophy_text'])
                @include('partials.about-info-card', ['iconKey' => 'cal_about_training_icon', 'headingKey' => 'cal_about_training_heading', 'textKey' => 'cal_about_training_text'])
                @include('partials.about-info-card', ['iconKey' => 'cal_about_pathways_icon', 'headingKey' => 'cal_about_pathways_heading', 'textKey' => 'cal_about_pathways_text'])
            </div>
        </div>
    </section>

    @if($storyHeading || count($storyParagraphs) > 0 || $storyVideo)
    <section class="cal-about-story">
        <div class="cal-about-shell">
            <div class="cal-about-story-grid">
                <div class="cal-about-story-text">
                    @if($storyHeading)<h2>{{ $storyHeading }}</h2>@endif
                    @if(count($storyParagraphs) > 0)
                    <div class="cal-about-story-content">
                        @foreach($storyParagraphs as $sp)
                        @php $p = trim($sp['paragraph'] ?? ''); @endphp
                        @if($p)<p>{{ $p }}</p>@endif
                        @endforeach
                    </div>
                    @endif
                </div>
                @if($storyVideo)
                <div class="cal-about-story-video">{!! $storyVideo !!}</div>
                @endif
            </div>
        </div>
    </section>
    @endif

    @if($ctaHeading)
    <section class="cal-about-cta">
        <div class="cal-about-shell cal-text-center">
            <h2>{{ $ctaHeading }}</h2>
            @if($ctaBtnText && $ctaBtnUrl)
            <a class="cal-btn cal-about-cta-btn" href="{{ cal_migrate_url($ctaBtnUrl) }}">{{ $ctaBtnText }}</a>
            @endif
        </div>
    </section>
    @endif

    @if(count($reviews) > 0)
    <section class="cal-about-reviews cal-bg-light">
        <div class="cal-about-shell">
            @if($reviewsHeading)<h2>{{ $reviewsHeading }}</h2>@endif
            <div class="cal-about-reviews-slider" data-about-reviews-slider>
                <button class="cal-about-reviews-arrow cal-about-reviews-prev" type="button" aria-label="{{ cal_about_ui_string('Previous review') }}">&#8249;</button>
                <div class="cal-about-reviews-viewport">
                    <div class="cal-about-reviews-track cal-grid">
                        @foreach($reviews as $review)
                        @php
                            $imageUrl = cal_image_url($review['image_url'] ?? '');
                            $videoUrl = cal_migrate_url(trim((string) ($review['video_url'] ?? '')));
                            $embedUrl = $videoUrl ? cal_youtube_embed_url($videoUrl) : '';
                        @endphp
                        <article class="cal-about-review-card cal-card">
                            @if($imageUrl)
                            <button class="cal-about-review-image cal-about-video-trigger" type="button"
                                @if($embedUrl) data-video="{{ $embedUrl }}" @endif
                                aria-label="Play video - {{ $review['name'] ?? 'Student review' }}">
                                <img src="{{ $imageUrl }}" alt="{{ $review['name'] ?? '' }}" loading="lazy" decoding="async">
                                @if($embedUrl)<span class="cal-about-play-icon">&#9654;</span>@endif
                            </button>
                            @endif
                            @if(!empty($review['name']))<h3>{{ $review['name'] }}</h3>@endif
                            @if(!empty($review['description']))<p>{{ $review['description'] }}</p>@endif
                        </article>
                        @endforeach
                    </div>
                </div>
                <button class="cal-about-reviews-arrow cal-about-reviews-next" type="button" aria-label="{{ cal_about_ui_string('Next review') }}">&#8250;</button>
            </div>
            @if($reviewsBtnText && $reviewsBtnUrl)
            <div class="cal-text-center"><a class="cal-btn cal-about-reviews-btn" href="{{ cal_migrate_url($reviewsBtnUrl) }}">{{ $reviewsBtnText }}</a></div>
            @endif
        </div>
    </section>
    @endif

    <div id="calVideoModal" class="cal-video-modal" role="dialog" aria-modal="true" aria-label="{{ cal_about_ui_string('Video review') }}" hidden>
        <div class="cal-video-modal-backdrop" data-cal-close-modal></div>
        <div class="cal-video-modal-content">
            <button class="cal-video-modal-close" type="button" data-cal-close-modal aria-label="{{ cal_about_ui_string('Close') }}">&times;</button>
            <div class="cal-video-modal-embed">
                <iframe id="calVideoIframe" src="" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen loading="lazy" style="display:none"></iframe>
                <video id="calVideoPlayer" src="" controls style="display:none;width:100%;height:100%;position:absolute;top:0;left:0" playsinline></video>
            </div>
        </div>
    </div>
</main>
@endsection
@push('scripts')
<script src="/js/cal-about-template.js?v=20260711" defer></script>
<script>
(function(){
    var modal = document.getElementById('calVideoModal');
    var iframe = document.getElementById('calVideoIframe');
    var player = document.getElementById('calVideoPlayer');
    if (!modal || !iframe || !player) return;
    var triggers = document.querySelectorAll('.cal-about-video-trigger');
    function closeModal() {
        modal.hidden = true;
        iframe.src = '';
        iframe.style.display = 'none';
        player.pause();
        player.src = '';
        player.style.display = 'none';
    }
    triggers.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var video = btn.getAttribute('data-video');
            if (!video) return;
            if (video.indexOf('video:') === 0) {
                iframe.style.display = 'none';
                iframe.src = '';
                player.src = video.slice(6);
                player.style.display = 'block';
                player.load();
                player.play().catch(function(){});
            } else {
                player.style.display = 'none';
                player.pause();
                player.src = '';
                iframe.src = video;
                iframe.style.display = 'block';
            }
            modal.hidden = false;
        });
    });
    modal.querySelectorAll('[data-cal-close-modal]').forEach(function(el) {
        el.addEventListener('click', closeModal);
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.hidden) closeModal();
    });
})();
</script>
@endpush