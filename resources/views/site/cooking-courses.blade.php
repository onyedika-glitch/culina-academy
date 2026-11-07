@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-cooking-courses-template.css?v=20260706">
@include('partials.booking-styles')
@endpush

@push('scripts')
@include('partials.booking-scripts')
@endpush

@section('content')
@php
    $heroTitle = cal_page_field('cal_cooking_courses_hero_title', $page->post_title ?? 'Cooking Courses');
    $pageContent = trim($page->post_content ?? '');
    $hasCoursesShortcode = str_contains($pageContent, 'culina_academy_courses');
@endphp

<main id="main" class="site-main cal-cooking-courses-template">
    @php cal_render_page_banner('cal-cooking', $heroTitle); @endphp

    <section class="cal-cooking-content">
        <div class="cal-cooking-content-inner">
            @php cal_render_breadcrumbs('cal-cooking-breadcrumbs'); @endphp

            @if($pageContent)
            <div class="cal-cooking-page-copy">
                {!! \App\Services\CulinaAcademy::fixContentUrls($pageContent) !!}
            </div>
            @endif

            @if(!$hasCoursesShortcode)
            <div class="cal-cooking-courses-listing">
                {!! \App\Services\CoursesListingService::render() !!}
            </div>
            @endif
        </div>
    </section>
</main>
@endsection