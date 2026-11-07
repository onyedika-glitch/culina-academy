@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-online-courses.css?v=20260714">
@endpush

@section('content')
<main id="main" class="site-main cal-online-page cal-online-detail-page">
    @php cal_render_page_banner('cal-online', $course['title']); @endphp

    <section class="cal-online-sub-bar">
        <div class="cal-online-shell">
            @php
                \App\Services\CulinaAcademy::renderBreadcrumbs('cal-online-breadcrumbs', [
                    ['url' => $listingPath, 'title' => $listingHeading],
                ], $course['title']);
            @endphp
        </div>
    </section>

    <section class="cal-online-content">
        <div class="cal-online-shell">
            <article class="cal-online-detail">
                @if(!empty($course['image']))
                <div class="cal-online-detail-image">
                    <img src="{{ $course['image'] }}" alt="{{ $course['title'] }}" loading="lazy">
                </div>
                @endif

                @if(!empty($course['price']) || !empty($course['duration']))
                <div class="cal-online-card-meta cal-online-detail-meta">
                    @if(!empty($course['duration']))
                    <span class="cal-online-meta-item cal-online-duration">{{ $course['duration'] }}</span>
                    @endif
                    @if(!empty($course['price']))
                    <span class="cal-online-meta-item cal-online-price">{{ $course['price'] }}</span>
                    @endif
                </div>
                @endif

                <div class="cal-online-detail-body">
                    {!! $course['content'] !!}
                </div>

                @if(!empty($course['features']))
                <p class="cal-online-card-features cal-online-detail-features">{{ $course['features'] }}</p>
                @endif

                <p class="cal-online-detail-back">
                    <a class="cal-online-btn" href="{{ $listingPath }}">&larr; {{ $listingHeading }}</a>
                </p>
            </article>
        </div>
    </section>
</main>
@endsection