@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-blog-template.css?v=20260706">
@endpush

@push('scripts')
<script>
window.calBlog = {
    ajaxUrl: '{{ cal_localized_path('/blog/load-more') }}',
    nonce: '{{ csrf_token() }}'
};
</script>
<script src="/js/cal-blog-template.js?v=20260706" defer></script>
@endpush

@section('content')
@php
    $heroTitle = $page->post_title ?? 'Blog';
    $pageContent = trim($page->post_content ?? '');
@endphp

<main id="main" class="site-main cal-blog-page">
    @php cal_render_page_banner('cal-blog', $heroTitle); @endphp

    <section class="cal-blog-sub-bar">
        <div class="cal-blog-shell">
            @php cal_render_breadcrumbs('cal-blog-breadcrumbs'); @endphp
        </div>
    </section>

    <section class="cal-blog-content">
        <div class="cal-blog-shell">
            @if($pageContent)
            <div class="cal-blog-intro">
                {!! \App\Services\CulinaAcademy::fixContentUrls($pageContent) !!}
            </div>
            @endif

            @if($posts->count() > 0)
            <div class="cal-blog-grid" id="cal-blog-grid">
                @foreach($posts as $post)
                @include('partials.blog-card', ['post' => $post])
                @endforeach
            </div>

            @if($posts->hasMorePages())
            <div class="cal-blog-loader is-hidden" id="cal-blog-loader">
                <span class="cal-blog-loading-text">Loading more posts...</span>
            </div>
            <span class="cal-blog-max-pages is-hidden">{{ $posts->lastPage() }}</span>
            @endif
            @endif
        </div>
    </section>
</main>
@endsection