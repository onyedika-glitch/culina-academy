@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-restaurant-partners-template.css?v=20260706">
@endpush

@section('content')
@php
    $heroTitle = cal_page_field('cal_rp_hero_title', $page->post_title ?? 'Restaurant Partners');
    $restaurants = \App\Services\RestaurantPartnersService::getRestaurants($page->ID);
@endphp

<main id="main" class="site-main cal-rp-page">
    @php cal_render_page_banner('cal-rp', $heroTitle); @endphp

    <section class="cal-rp-content">
        <div class="cal-rp-shell">
            @php cal_render_breadcrumbs('cal-rp-breadcrumbs'); @endphp

            @if(count($restaurants) > 0)
            <div class="cal-rp-grid">
                @foreach($restaurants as $restaurant)
                @include('partials.restaurant-partner-card', ['restaurant' => $restaurant])
                @endforeach
            </div>
            @endif
        </div>
    </section>
</main>
@endsection