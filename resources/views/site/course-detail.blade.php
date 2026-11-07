@extends('layouts.app')

@php
    $courseId = $course->ID;
    $lang = \App\Services\PolylangService::currentLanguage();
    $hours = \App\Services\AcfFieldService::getField($courseId, 'ca_hours');
    $duration = \App\Services\CourseDisplayService::duration(
        (string) \App\Services\AcfFieldService::getField($courseId, 'ca_duration'),
        $lang
    );
    $location = \App\Services\CourseDisplayService::location(
        (string) \App\Services\AcfFieldService::getField($courseId, 'ca_location'),
        $lang
    );
    $price = cal_format_price($courseId);
    $description = \App\Services\CourseDisplayService::resolveCourseDescription($course);
    $downloadUrl = cal_migrate_url((string) \App\Services\AcfFieldService::getField($courseId, 'ca_download_program_url', ''));
    $enquireUrl = cal_migrate_url((string) \App\Services\AcfFieldService::getField($courseId, 'ca_enquire_url', ''));
    $reserveUrl = cal_migrate_url((string) \App\Services\AcfFieldService::getField($courseId, 'ca_reserve_url', ''));
    $reserveText = \App\Services\AcfFieldService::getField($courseId, 'ca_reserve_text', '');
    $imageMeta = \App\Services\AcfFieldService::getField($courseId, 'ca_image_url');
    $courseImage = cal_image_url($imageMeta);
    $durationLine = \App\Services\CulinaAcademy::formatCourseDuration($hours, $duration, $lang);
    $priceValue = preg_replace('/[^0-9.]/', '', html_entity_decode(strip_tags((string) $price), ENT_QUOTES, 'UTF-8'));
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'Course',
        'name' => $course->post_title,
        'description' => strip_tags((string) ($description ?: $course->post_excerpt ?: $course->post_content)),
        'provider' => [
            '@type' => 'Organization',
            'name' => 'The Culina Academy',
            'sameAs' => url('/'),
        ],
        'location' => [
            '@type' => 'Place',
            'name' => $location ?: 'Your City',
        ],
        'offers' => array_filter([
            '@type' => 'Offer',
            'category' => 'Paid',
            'price' => $priceValue !== '' ? $priceValue : null,
            'priceCurrency' => $priceValue !== '' ? 'GBP' : null,
        ]),
    ];
    if ($courseImage) {
        $jsonLd['image'] = url($courseImage);
    }
@endphp

@push('styles')
<link rel="stylesheet" href="/css/cal-single-course.css?v=20260706">
@endpush

@push('json-ld')
<script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<main class="cal-course-page">
    @php \App\Services\CulinaAcademy::renderPageBanner('cal-course', $course->post_title); @endphp

    <section class="cal-course-content">
        <div class="cal-course-main">
            @php \App\Services\CulinaAcademy::renderBreadcrumbs('cal-course-breadcrumbs', [
                ['url' => '/cooking-courses', 'title' => cal_i18n_text('Cooking Courses')],
            ], $course->post_title); @endphp

            @if($courseImage)
            <div class="cal-course-featured-image">
                <img src="{{ $courseImage }}" alt="{{ $course->post_title }}" loading="lazy">
            </div>
            @endif

            <div class="cal-course-copy">
                @if($description)
                {!! \App\Services\CulinaAcademy::fixContentUrls($description) !!}
                @endif
            </div>

            @if($downloadUrl)
            <p class="cal-course-download-cta">
                <a class="cal-course-button cal-btn-download" href="{{ $downloadUrl }}" target="_blank" rel="noopener">{{ cal_i18n_text('Download programme') }}</a>
            </p>
            @endif
        </div>
    </section>

    <section class="cal-course-details">
        <div class="cal-course-details-inner">
            <div class="cal-course-dates">
                {!! \App\Services\CourseIntakeService::renderHtml($courseId) !!}
            </div>

            <div class="cal-course-facts">
                @if($location)
                <div class="cal-fact-row cal-fact-location">
                    <span class="cal-fact-label">{{ cal_i18n_text('Location:') }}</span>
                    <span class="cal-fact-value">{{ $location }}</span>
                </div>
                @endif

                @if($durationLine)
                <div class="cal-fact-row cal-fact-duration">
                    <span class="cal-fact-label">{{ cal_i18n_text('Duration:') }}</span>
                    <span class="cal-fact-value">{{ $durationLine }}</span>
                </div>
                @endif

                @if($price)
                <div class="cal-fact-row cal-fact-price">
                    <span class="cal-fact-label">{{ cal_i18n_text('Price:') }}</span>
                    <span class="cal-fact-value">{!! $price !!}</span>
                </div>
                @endif
            </div>

            @if($enquireUrl)
            <a class="cal-course-button cal-btn-enquire" href="{{ $enquireUrl }}" target="_blank" rel="noopener">{{ cal_i18n_text('Enquire now') }}</a>
            @endif

            @if($reserveText)
            <p class="cal-reserve-text">{{ $reserveText }}</p>
            @endif

            @if($reserveUrl)
            <a class="cal-course-button cal-btn-reserve" href="{{ $reserveUrl }}" target="_blank" rel="noopener">{{ cal_i18n_text('Reserve your course now') }}</a>
            @endif
        </div>
    </section>
</main>
@endsection