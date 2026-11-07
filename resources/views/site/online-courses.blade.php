@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-online-courses.css?v=20260706">
@endpush

@section('content')
@php
    $heading = cal_page_field('cal_online_heading', $page->post_title ?? 'Online Courses');
    $subtitle = cal_page_field('cal_online_subtitle');
    $courses = cal_page_field('cal_online_courses', []);
@endphp

<main id="main" class="site-main cal-online-page">
    @php cal_render_page_banner('cal-online', $heading); @endphp

    <section class="cal-online-sub-bar">
        <div class="cal-online-shell">
            @php cal_render_breadcrumbs('cal-online-breadcrumbs'); @endphp
        </div>
    </section>

    <section class="cal-online-content">
        <div class="cal-online-shell">
            @if($subtitle)
            <p class="cal-online-intro">{{ $subtitle }}</p>
            @endif

            @if(is_array($courses) && count($courses) > 0)
            <div class="cal-online-grid">
                @foreach($courses as $course)
                @php
                    $title = $course['title'] ?? '';
                    $image = cal_image_url($course['image'] ?? '');
                    $rawLinkUrl = trim((string) ($course['link_url'] ?? ''));
                    // Use external URL directly; only link to local detail page if no external URL
                    if ($rawLinkUrl !== '' && (str_starts_with($rawLinkUrl, 'http://') || str_starts_with($rawLinkUrl, 'https://'))) {
                        $linkUrl = cal_migrate_url($rawLinkUrl);
                    } else {
                        $courseSlug = \App\Services\OnlineCourseService::courseSlugFromCard($course);
                        $linkUrl = $courseSlug
                            ? \App\Services\OnlineCourseService::detailPath($courseSlug)
                            : cal_migrate_url($rawLinkUrl);
                    }
                    $linkText = $course['link_text'] ?: cal_i18n_text('See course details');
                @endphp
                <article class="cal-online-card">
                    <div class="cal-online-card-body">
                        @if($image)
                        <div class="cal-online-card-image">
                            <img src="{{ $image }}" alt="{{ $title }}" loading="lazy">
                        </div>
                        @endif
                        <div class="cal-online-card-text">
                            @if($title)<h2>{{ $title }}</h2>@endif
                            @if(!empty($course['description']))
                            <div class="cal-online-card-desc">{!! $course['description'] !!}</div>
                            @endif
                            @if(!empty($course['duration']) || !empty($course['price']))
                            <div class="cal-online-card-meta">
                                @if(!empty($course['duration']))
                                <span class="cal-online-meta-item cal-online-duration">{{ $course['duration'] }}</span>
                                @endif
                                @if(!empty($course['price']))
                                <span class="cal-online-meta-item cal-online-price">{{ $course['price'] }}</span>
                                @endif
                            </div>
                            @endif
                            @if(!empty($course['features']))
                            <p class="cal-online-card-features">{{ $course['features'] }}</p>
                            @endif
                            @if($linkUrl)
                            <a class="cal-online-btn" href="{{ $linkUrl }}">{{ $linkText }}</a>
                            @endif
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
            @endif

            @php $content = trim($page->post_content ?? ''); @endphp
            @if($content)
            <div class="cal-online-page-content">
                {!! \App\Services\CulinaAcademy::fixContentUrls($content) !!}
            </div>
            @endif
        </div>
    </section>
</main>
@endsection