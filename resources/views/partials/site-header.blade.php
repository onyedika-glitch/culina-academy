@php
    $phone = cal_option('cal_header_phone');
    $email = cal_option('cal_header_email');
    $logo = cal_image_url(cal_option('cal_header_logo'));
    $languageLinks = \App\Services\PolylangService::getLanguageSwitcherLinks();
@endphp

@include('partials.mobile-flags-bar')

<div class="crisp-nav">
    @if($phone || $email || count($languageLinks) > 1)
    <div class="crisp-nav-topbar">
        <div class="crisp-nav-topbar-container">
            @if($phone || $email)
            <ul class="crisp-nav-contact-info">
                @if($phone)
                <li>{!! cal_phone_icon() !!}{{ $phone }}</li>
                @endif
                @if($email)
                <li>{!! cal_email_icon() !!}{{ $email }}</li>
                @endif
            </ul>
            @endif
            @include('partials.language-switcher')
        </div>
    </div>
    @endif

    <header class="crisp-nav-header">
        <div class="crisp-nav-container">
            @if($logo)
            <div class="crisp-nav-logo">
                <a href="{{ cal_localized_path('/') }}" class="crisp-nav-logo-link" aria-label="Go to homepage">
                    <img src="{{ $logo }}" alt="The Culina Academy" width="180" height="50" decoding="async">
                </a>
            </div>
            @endif
            <button class="crisp-nav-mobile-toggle" aria-label="Open Menu">
                {!! cal_menu_icon() !!}
            </button>
            <nav class="crisp-nav-nav" aria-label="Primary">
                <button class="crisp-nav-mobile-close" aria-label="Close Menu">
                    {!! cal_close_icon() !!}
                </button>
                {!! \App\Services\MenuService::primaryMenuHtml() !!}
            </nav>
            <div class="crisp-nav-mobile-overlay"></div>
        </div>
    </header>
</div>