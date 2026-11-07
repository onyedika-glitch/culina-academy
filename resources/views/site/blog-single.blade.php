@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-blog-template.css?v=20260706">
@endpush

@section('content')
@php
    $date = $post->post_date ? date('F j, Y', strtotime($post->post_date)) : '';
    $thumbId = $post->getMeta('_thumbnail_id');
    $thumbUrl = $thumbId ? \App\Services\AcfFieldService::attachmentUrl((int) $thumbId) : '';
@endphp

<main id="main" class="site-main cal-single-post">
    <section class="cal-single-breadcrumb-bar">
        <div class="cal-single-container">
            <nav class="cal-single-breadcrumb" aria-label="Breadcrumb">
                <span class="cal-single-breadcrumb-icon" aria-hidden="true">&#8962;</span>
                <a href="{{ cal_localized_path('/') }}">Home</a>
                <span class="cal-single-breadcrumb-sep">/</span>
                <a href="{{ cal_localized_path('/blog') }}">Blog</a>
                <span class="cal-single-breadcrumb-sep">/</span>
                <span class="cal-single-breadcrumb-current">{{ $post->post_title }}</span>
            </nav>
        </div>
    </section>

    <section class="cal-single-content-area">
        <div class="cal-single-container">
            <header class="cal-single-header">
                <h1 class="cal-single-title">{{ $post->post_title }}</h1>
            </header>

            @if($date)
            <div class="cal-single-action-bar">
                <div class="cal-single-action-bar-inner">
                    <span class="cal-single-btn">{{ $date }}</span>
                </div>
            </div>
            @endif

            @if($thumbUrl)
            <div class="cal-single-featured" style="margin-bottom:32px">
                <img src="{{ $thumbUrl }}" alt="{{ $post->post_title }}" style="width:100%;height:auto;border-radius:4px">
            </div>
            @endif

            <div class="cal-single-body">
                {!! \App\Services\CulinaAcademy::fixContentUrls($post->post_content ?? '') !!}
            </div>

            <div class="cal-single-footer-border" aria-hidden="true"></div>

            @if(isset($recent) && $recent->count())
            <div style="margin-top:40px">
                <h2 style="font-size:1.4rem;margin-bottom:20px">Recent Posts</h2>
                <div class="cal-blog-grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr))">
                    @foreach($recent as $recentPost)
                    @include('partials.blog-card', ['post' => $recentPost])
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </section>
</main>
@endsection