@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-policy-pages.css?v=20260706">
@endpush

@section('content')
@php
    $contentHeading = cal_page_field('cal_howto_hero_heading');
    $content = cal_page_field('cal_howto_content');
    $enquireUrl = cal_migrate_url((string) cal_page_field('cal_howto_enquire_url', '/course-enrollment'));
    $enquireText = cal_page_field('cal_howto_enquire_text', 'Enquire now');
@endphp

<main id="main" class="site-main cal-policy-page">
    @php cal_render_page_banner('cal-policy', $page->post_title ?? 'How to Apply'); @endphp

    <section class="cal-policy-sub-bar">
        <div class="cal-policy-shell">
            @php cal_render_breadcrumbs('cal-policy-breadcrumbs'); @endphp
        </div>
    </section>

    <section class="cal-policy-content">
        <div class="cal-policy-shell">
            <div class="cal-policy-entry">
                @if($contentHeading)
                <h2>{{ $contentHeading }}</h2>
                @endif
                @if($content)
                {!! \App\Services\CulinaAcademy::fixContentUrls($content) !!}
                @else
                {!! \App\Services\CulinaAcademy::theContent() !!}
                @endif
            </div>
            @if($enquireUrl && $enquireText)
            <div class="cal-howto-enquire-wrap">
                <a class="cal-howto-enquire-btn" href="{{ $enquireUrl }}">
                    <span class="cal-howto-enquire-text">{{ $enquireText }}</span>
                </a>
            </div>
            @endif
        </div>
    </section>
</main>
@endsection