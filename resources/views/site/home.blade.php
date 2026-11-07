@extends('layouts.app')

@section('lean-assets')
@endsection

@section('defer-header-js')
@endsection

@section('minimal-fonts')
@endsection

@section('critical-css')
<style>
/* ── Critical CSS for homepage (dimensions match cal-home-template.css) ── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth;scroll-padding-top:127px}
body{font-family:-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif;font-weight:400;font-size:17px;line-height:1.5;color:#1f2937;background:#fff;padding-top:127px}
.crisp-nav-topbar{background:#1a1a1a;color:#fff;padding:8px 0;font-size:14px;position:fixed;top:0;left:0;width:100%;z-index:1002}
.crisp-nav-topbar-container{max-width:1440px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;padding:0 20px}
.crisp-nav-contact-info{display:flex;list-style:none;gap:25px;align-items:center;margin:0}
.crisp-nav-contact-info li{display:flex;align-items:center;gap:8px;white-space:nowrap;color:#fff}
.crisp-nav-lang-switcher{display:flex;list-style:none;gap:10px;align-items:center;margin:0}
.crisp-nav-lang-switcher a{color:#ffb703;text-decoration:none;font-size:13px}
.crisp-nav-header{background:#fff;position:fixed;top:37px;left:0;width:100%;z-index:1001;box-shadow:0 2px 8px rgba(0,0,0,.08)}
.crisp-nav-container{max-width:1440px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;padding:15px 20px;min-height:70px}
.crisp-nav-logo img{max-height:60px;width:auto;display:block}
.crisp-nav-nav{display:none}
.wpcul-slider{position:relative;overflow:hidden;width:100%;height:860px;min-height:640px;max-height:calc(100vh - 0px);background:#0a0d14}
.wpcul-track{display:flex;width:100%;height:100%}
.wpcul-slide{position:relative;flex:0 0 100%;height:100%;display:flex;align-items:center;padding:128px 8% 76px}
.wpcul-slide-bg{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.wpcul-content{position:relative;z-index:2;color:#fff;max-width:560px}
.wpcul-content h1,.wpcul-content h2{font-size:63px;font-weight:700;line-height:1.02;margin-bottom:22px;text-shadow:0 3px 12px rgba(0,0,0,.45)}
.wpcul-content p{font-size:14px;line-height:1.55;margin-bottom:31px;text-shadow:0 3px 12px rgba(0,0,0,.4)}
.wpcul-btn{display:inline-block;background:#ffb703;color:#fff;padding:12px 32px;border-radius:4px;font-weight:700;font-size:14px;text-decoration:none}
.wpcul-dots{display:flex;justify-content:center;gap:10px;min-height:8px}
.wpcul-dot{width:8px;height:8px;border:0;border-radius:50%;background:rgba(255,255,255,.3);padding:0}
.wpcul-dot.active{background:#ffb703;width:18px;border-radius:10px}
img{max-width:100%;height:auto}
a{color:inherit;text-decoration:none}
.cal-home-carousel-section{min-height:520px}
.cal-enquiry-section{min-height:825px}
.cal-why-choose-section{min-height:400px}
.reviews-section{min-height:500px;padding:104px 24px 118px;background:#f7f8fa}
.slider-dots{display:flex;justify-content:center;gap:18px;margin-top:46px;min-height:8px}
.slider-dot{width:8px;height:8px;border:none;border-radius:50%;background:#d1d5db;padding:0}
.slider-dot.active{background:#ffb703;width:18px;border-radius:10px}
@media(max-width:1024px){
html{scroll-padding-top:153px}
body{padding-top:153px}
.crisp-nav-topbar{display:none}
.crisp-nav-header{top:74px}
.crisp-nav-mobile-flags-bar{display:flex;position:fixed;top:0;left:0;width:100%;height:74px;background:#1a1a1a;z-index:1003}
}
@media(max-width:768px){
.wpcul-slider{height:55vh!important;max-height:450px!important;min-height:320px!important}
.wpcul-content h1,.wpcul-content h2{font-size:clamp(22px,6vw,32px)!important}
.wpcul-content p{font-size:clamp(14px,3.5vw,16px)!important}
.wpcul-btn{font-size:14px!important;padding:12px 28px!important}
}
</style>
@endsection

@push('head')
@php
    $slides = cal_page_field('cal_home_slides', []);
    $firstSlideImage = null;
    if (is_array($slides) && count($slides) > 0) {
        $firstSlideImage = cal_hero_image_src($slides[0]['image'] ?? '');
    }
@endphp
@php $heroSrcset = cal_image_srcset($slides[0]['image'] ?? '', 1280); @endphp
@if($firstSlideImage && $firstSlideImage['url'] !== '')
<link rel="preload" as="image"
      href="{{ $firstSlideImage['url'] }}"
      @if($heroSrcset) imagesrcset="{{ $heroSrcset }}" @endif
      imagesizes="(max-width:768px) 100vw, 1280px"
      fetchpriority="high">
@endif
@endpush

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/css/intlTelInput.css">
<link rel="stylesheet" href="/css/cal-home-template.css?v=20260715-2">
<link rel="stylesheet" href="/css/cacx-carousel.css?v=20260713" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="/css/cacx-carousel.css?v=20260713"></noscript>
@endpush

@push('scripts')
<script>
window.calHomeAjax = {
    ajaxurl: '{{ cal_localized_path('/contact-us') }}',
    nonce: '',
    language: 'en',
    requiredText: 'Please fill in all required fields.',
    sendingText: 'Sending...',
    errorText: 'Something went wrong. Please try again.',
    enquireText: 'Enquire now',
    bannerLabel: 'Go to banner slide',
    reviewLabel: 'Go to review'
};
</script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/intlTelInput.min.js" defer></script>
<script src="/js/cal-home-template.js?v=20260715-2" defer></script>
@endpush

@section('content')
@php
    $slides = cal_page_field('cal_home_slides', []);
    $whyTitle = cal_page_field('cal_home_why_title');
    $whySubtitle = cal_page_field('cal_home_why_subtitle');
    $whyCards = cal_page_field('cal_home_why_cards', []);
    $whyButton = cal_page_field('cal_home_why_button', []);
    $enquiryBgMeta = cal_page_field('cal_home_enquiry_bg');
    $enquiryBg = cal_image_src($enquiryBgMeta, 'large');
    $enquiryEyebrow = cal_page_field('cal_home_enquiry_eyebrow');
    $enquiryHeading = cal_page_field('cal_home_enquiry_heading');
    $enquiryFormText = cal_page_field('cal_home_enquiry_form_text');
    $enquiryBtnText = cal_page_field('cal_home_enquiry_btn_text', 'ENQUIRE NOW');
    $testimonialsHeading = cal_page_field('cal_home_testimonials_heading');
    $testimonials = cal_page_field('cal_home_testimonials', []);
    $testimonialsCta = cal_page_field('cal_home_testimonials_cta', []);
    $btnUrl = !empty($whyButton['url']) ? cal_migrate_url($whyButton['url']) : '';
    $btnText = $whyButton['title'] ?? '';
    $ctaUrl = !empty($testimonialsCta['url']) ? cal_migrate_url($testimonialsCta['url']) : '/reviews';
    $ctaTextRaw = $testimonialsCta['title'] ?? 'More Reviews';
    $ctaText = in_array($ctaTextRaw, ['Read More Reviews', 'View More Reviews'], true)
        ? 'More Reviews'
        : $ctaTextRaw;
    $ctaText = cal_i18n_text($ctaText);
@endphp

<main id="main" class="site-main cal-home-template">
    @if(is_array($slides) && count($slides) > 0)
    <section class="wpcul-slider">
        <div class="wpcul-track">
            @foreach($slides as $index => $slide)
            @php
                $imageMeta = $slide['image'] ?? '';
                $imageSrc = cal_hero_image_src($imageMeta);
                $image = $imageSrc['url'] ?? cal_image_url($imageMeta);
                $isFirst = $index === 0;
                $srcset = $isFirst ? cal_image_srcset($imageMeta, 1280) : '';
                $sizeAttrs = cal_image_attrs($imageMeta, 'large');
                $button = $slide['button'] ?? [];
            @endphp
            @if($image)
            <div class="wpcul-slide">
                @if($isFirst)
                <img class="wpcul-slide-bg"
                     src="{{ $image }}"
                     @if($srcset) srcset="{{ $srcset }}" sizes="(max-width:768px) 100vw, 1280px" @endif
                     alt="{{ $slide['title'] ?? 'The Culina Academy - ' . ($slide['eyebrow'] ?? 'Professional Chef Courses') }}"
                     {!! $sizeAttrs !!}
                     loading="eager"
                     decoding="async"
                     fetchpriority="high">
                @else
                <img class="wpcul-slide-bg wpcul-slide-deferred"
                     data-src="{{ $image }}"
                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 7'%3E%3C/svg%3E"
                     alt="{{ $slide['title'] ?? 'The Culina Academy - ' . ($slide['eyebrow'] ?? 'Professional Chef Courses') }}"
                     {!! $sizeAttrs !!}
                     loading="lazy"
                     decoding="async"
                     fetchpriority="low">
                @endif
                <div class="wpcul-content">
                    @if(!empty($slide['eyebrow']) || !empty($slide['title']))
                    @if($isFirst)<h1>@else<h2>@endif
                        @if(!empty($slide['eyebrow']))<span>{{ $slide['eyebrow'] }}</span>@endif
                        {{ $slide['title'] ?? '' }}
                    @if($isFirst)</h1>@else</h2>@endif
                    @endif
                    @if(!empty($slide['text']))<p>{{ $slide['text'] }}</p>@endif
                    @if(!empty($button['url']))
                    @php
                        // Make button URL locale-aware: strip any locale prefix for route-based URLs
                        $btnUrl = $button['url'];
                        $btnUrl = preg_replace('#^https?://[^/]+/(en|fr|es|it|zh|zh-tw)/cooking-courses#', '/cooking-courses', $btnUrl);
                        $btnUrl = preg_replace('#^/(en|fr|es|it|zh|zh-tw)/cooking-courses#', '/cooking-courses', $btnUrl);
                        if ($btnUrl === '/cooking-courses') {
                            $btnUrl = cal_localized_path('/cooking-courses');
                        } else {
                            $btnUrl = cal_migrate_url($button['url']);
                        }
                    @endphp
                    <a href="{{ $btnUrl }}" class="wpcul-btn">{{ \App\Services\UiTranslationService::translateMenuTitle($button['title'] ?? 'View Our Courses') }}</a>
                    @endif
                </div>
            </div>
            @endif
            @endforeach
        </div>
        @if(count($slides) > 1)
        <div class="wpcul-nav">
            <button class="wpcul-prev" type="button" aria-label="Previous slide"></button>
            <button class="wpcul-next" type="button" aria-label="Next slide"></button>
        </div>
        <div class="wpcul-dots">
            @php $dotIndex = 0; @endphp
            @foreach($slides as $dotSlide)
            @php
                $dotImageMeta = $dotSlide['image'] ?? '';
                $dotImageSrc = cal_hero_image_src($dotImageMeta);
                $dotImage = $dotImageSrc['url'] ?? cal_image_url($dotImageMeta);
            @endphp
            @if($dotImage)
            <button type="button"
                    class="wpcul-dot{{ $dotIndex === 0 ? ' active' : '' }}"
                    aria-label="Go to banner slide {{ $dotIndex + 1 }}"
                    @if($dotIndex === 0) aria-current="true" @endif></button>
            @php $dotIndex++; @endphp
            @endif
            @endforeach
        </div>
        @endif
    </section>
    @endif

    <section class="cal-home-carousel-section">
        {!! \App\Services\CarouselService::render() !!}
    </section>

    <section class="cal-enquiry-section">
        <div class="cal-enquiry-bg">
            @if(!empty($enquiryBg['url']))
            <img class="cal-enquiry-bg-img"
                 src="{{ $enquiryBg['url'] }}"
                 alt=""
                 role="presentation"
                 @if(!empty($enquiryBg['width']) && !empty($enquiryBg['height'])) width="{{ $enquiryBg['width'] }}" height="{{ $enquiryBg['height'] }}" @endif
                 loading="lazy"
                 decoding="async">
            @endif
            <div class="cal-enquiry-overlay"></div>
            <div class="cal-enquiry-inner">
                <div class="cal-enquiry-text">
                    @if($enquiryEyebrow)<span class="cal-enquiry-eyebrow">{{ $enquiryEyebrow }}</span>@endif
                    @if($enquiryHeading)<h2 class="cal-enquiry-heading">{{ $enquiryHeading }}</h2>@endif
                </div>
                <div class="cal-enquiry-form-wrap">
                    <div class="cal-enquiry-form-header">
                        @if($enquiryFormText)<p>{{ $enquiryFormText }}</p>@endif
                    </div>
                    <div class="cal-enquiry-form-body">
                        <form class="cal-enquiry-form" action="{{ cal_localized_path('/contact-us') }}" method="post" novalidate>
                            @csrf
                            <input type="text" name="website" class="ca-hp-field" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;opacity:0">
                            <input type="hidden" name="form_started" value="{{ time() }}">
                            <div class="cal-enquiry-field">
                                <label for="cal-enquiry-name">{{ cal_i18n_text('Your Name') }}</label>
                                <input id="cal-enquiry-name" type="text" name="name" autocomplete="name" required>
                            </div>
                            <div class="cal-enquiry-field">
                                <label for="cal-enquiry-email">Email</label>
                                <input id="cal-enquiry-email" type="email" name="email" autocomplete="email" required>
                            </div>
                            <div class="cal-enquiry-field ca-phone-row">
                                <label for="cal-enquiry-phone">{{ cal_i18n_text('Phone / WhatsApp') }}</label>
                                <input type="hidden" class="ca-intl-code" name="country_code" value="44">
                                <input id="cal-enquiry-phone" type="tel" name="phone" autocomplete="tel" required>
                            </div>
                            <div class="cal-enquiry-field">
                                <button type="submit" class="cal-enquiry-submit">{{ $enquiryBtnText }}</button>
                            </div>
                            <div class="cal-enquiry-note" role="status" aria-live="polite"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cal-why-choose-section">
        <div class="cal-why-choose-inner">
            <header class="cal-why-choose-header">
                @if($whyTitle)<h2>{{ $whyTitle }}</h2>@endif
                @if($whySubtitle)<p class="cal-why-choose-subtitle">{{ $whySubtitle }}</p>@endif
            </header>
            @if(is_array($whyCards) && count($whyCards) > 0)
            <div class="cal-why-choose-grid">
                @foreach($whyCards as $card)
                <article class="cal-why-choose-card">
                    @php
                        $iconSrc = cal_image_src($card['icon'] ?? '', 'thumbnail');
                        $iconUrl = $iconSrc['url'] ?? cal_image_url($card['icon'] ?? '');
                    @endphp
                    @if($iconUrl)
                    <div class="cal-why-choose-icon">
                        <img src="{{ $iconUrl }}" alt="{{ $card['title'] ?? '' }}" width="48" height="48" loading="lazy" decoding="async">
                    </div>
                    @endif
                    @if(!empty($card['title']))<h3>{{ $card['title'] }}</h3>@endif
                    @if(!empty($card['text']))<p>{{ $card['text'] }}</p>@endif
                </article>
                @endforeach
            </div>
            @endif
            @if($btnText && $btnUrl)
            <div class="cal-why-choose-action">
                <a href="{{ $btnUrl }}" class="cal-why-choose-button">{{ $btnText }}</a>
            </div>
            @endif
        </div>
    </section>

    @if(is_array($testimonials) && count($testimonials) > 0)
    <section class="reviews-section">
        <div class="reviews-header">
            @if($testimonialsHeading)<h2 class="reviews-heading">{{ $testimonialsHeading }}</h2>@endif
        </div>
        <div class="reviews-slider-wrapper">
            <div class="reviews-slider-track" id="reviewsTrack">
                @foreach($testimonials as $review)
                <div class="review-card">
                    <div class="review-content">
                        <p class="review-text">"{{ $review['quote'] ?? '' }}"</p>
                        <div class="review-author">
                            <span class="author-name">{{ $review['name'] ?? '' }}</span>
                            <span class="author-country">{{ $review['location'] ?? '' }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
                @if(count($testimonials) > 1)
                @foreach($testimonials as $review)
                <div class="review-card" aria-hidden="true">
                    <div class="review-content">
                        <p class="review-text">"{{ $review['quote'] ?? '' }}"</p>
                        <div class="review-author">
                            <span class="author-name">{{ $review['name'] ?? '' }}</span>
                            <span class="author-country">{{ $review['location'] ?? '' }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
            @if(count($testimonials) > 1)
            <div class="slider-dots" id="sliderDots">
                @foreach($testimonials as $dotIndex => $review)
                <button type="button"
                        class="slider-dot{{ $dotIndex === 0 ? ' active' : '' }}"
                        aria-label="Go to review {{ $dotIndex + 1 }}"></button>
                @endforeach
            </div>
            @endif
        </div>
        @if($ctaText && $ctaUrl)
        <div class="reviews-footer">
            <a href="{{ $ctaUrl }}" class="reviews-cta-btn">{{ $ctaText }}</a>
        </div>
        @endif
    </section>
    @endif

    @php $content = trim($page->post_content ?? ''); @endphp
    @if($content)
    <section class="cal-home-page-content">
        {!! \App\Services\CulinaAcademy::fixContentUrls($content) !!}
    </section>
    @endif
</main>
@endsection

@push('scripts')
<script>
window.calHomeAjax = {
    ajaxurl: '{{ cal_localized_path('/contact-us') }}',
    nonce: '',
    language: 'en',
    requiredText: 'Please fill in all required fields.',
    sendingText: 'Sending...',
    errorText: 'Something went wrong. Please try again.',
    enquireText: 'Enquire now',
    bannerLabel: 'Go to banner slide',
    reviewLabel: 'Go to review'
};
</script>
<script>
(function () {
    function loadScript(src, onload) {
        if (document.querySelector('script[data-cal-src="' + src + '"]')) {
            if (typeof onload === 'function') onload();
            return;
        }
        var s = document.createElement('script');
        s.src = src;
        s.defer = true;
        s.setAttribute('data-cal-src', src);
        if (typeof onload === 'function') {
            s.onload = onload;
        }
        document.body.appendChild(s);
    }
    function afterIdle(fn, timeout) {
        if ('requestIdleCallback' in window) {
            requestIdleCallback(fn, { timeout: timeout || 3000 });
        } else {
            window.addEventListener('load', fn, { once: true });
        }
    }

    var carouselSection = document.querySelector('.cal-home-carousel-section');
    if (carouselSection && 'IntersectionObserver' in window) {
        var carouselObserver = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) {
                loadScript('/js/cacx-carousel.js?v=20260713');
                carouselObserver.disconnect();
            }
        }, { rootMargin: '320px 0px' });
        carouselObserver.observe(carouselSection);
    } else {
        afterIdle(function () {
            loadScript('/js/cacx-carousel.js?v=20260713');
        }, 3500);
    }
})();
</script>
@endpush