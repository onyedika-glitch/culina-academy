@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-reviews-template.css?v=20260706">
@endpush

@push('scripts')
<script>
window.calReviews = { unsupportedVideoText: 'Your browser does not support HTML5 video.' };
</script>
<script src="/js/cal-reviews-template.js?v=20260706" defer></script>
@endpush

@section('content')
@php
    $heroTitle = cal_page_field('cal_reviews_hero_title', $page->post_title ?? 'Reviews');
    $heading = cal_page_field('cal_reviews_heading');
    $subheading = cal_page_field('cal_reviews_subheading');
    $reviews = cal_page_field('cal_reviews_list', []);
    $reviews = is_array($reviews) ? $reviews : [];
@endphp

<main id="main" class="site-main cal-reviews-page">
    @php cal_render_page_banner('cal-reviews', $heroTitle); @endphp

    <section class="cal-reviews-content">
        <div class="cal-reviews-shell">
            @php cal_render_breadcrumbs('cal-reviews-breadcrumbs'); @endphp

            <div class="reviews">
                @if($heading)
                <div class="reviews-header">
                    <h2>{{ $heading }}</h2>
                    @if($subheading)
                    <div class="subheading">{{ $subheading }}</div>
                    @endif
                </div>
                @endif

                @if(count($reviews) > 0)
                <div class="video-grid" id="videoGrid">
                    @foreach($reviews as $review)
                    @php
                        $name = trim((string) ($review['name'] ?? ''));
                        $country = trim((string) ($review['country'] ?? ''));
                        $course = trim((string) ($review['course'] ?? ''));
                        $type = trim((string) ($review['video_type'] ?? ''));
                        $youtube = trim((string) ($review['youtube_id'] ?? ''));
                        $mp4 = cal_migrate_url(trim((string) ($review['mp4_url'] ?? '')));
                        $image = cal_image_url($review['preview_image'] ?? '');
                        $videoUrl = $type === 'mp4' ? $mp4 : ($type === 'youtube' ? $youtube : '');
                    @endphp
                    @if($name && $videoUrl)
                    <article class="card">
                        <button class="card-video-trigger" type="button" data-video-type="{{ $type }}" data-video-src="{{ $videoUrl }}" aria-label="Play video testimonial from {{ $name }}">
                            <span class="video-thumbnail">
                                @if($image)
                                <img src="{{ $image }}" alt="" loading="lazy">
                                @endif
                                <span class="play-overlay">
                                    <span class="play-btn-circle">
                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M8 5v14l11-7z"></path></svg>
                                    </span>
                                </span>
                            </span>
                        </button>
                        <div class="card-content">
                            <h4>{{ $name }}</h4>
                            @if($country || $course)
                            <p>
                                @if($country)
                                <span class="country">{{ $country }}</span>
                                @endif
                                @if($country && $course) - @endif
                                {{ $course }}
                            </p>
                            @endif
                        </div>
                    </article>
                    @endif
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </section>

    <div class="modal" id="modal" role="dialog" aria-modal="true" aria-labelledby="reviewsModalTitle" aria-hidden="true">
        <div class="player-wrapper" role="document">
            <h2 class="screen-reader-text" id="reviewsModalTitle">Video testimonial</h2>
            <button class="close-btn" id="closeModalBtn" aria-label="Close">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
            <div id="player"></div>
        </div>
    </div>
</main>
@endsection