@php
    $links = \App\Services\PolylangService::getLanguageSwitcherLinks();
    $phone = cal_option('cal_header_phone');
    $email = cal_option('cal_header_email');
@endphp

@if(count($links) > 1)
<div class="crisp-nav-mobile-flags-bar" role="navigation" aria-label="{{ cal_i18n_text('Language switcher') }}">
    <div class="crisp-nav-mobile-flags-right">
        @foreach($links as $link)
        <a href="{{ $link['url'] }}"
           hreflang="{{ $link['slug'] }}"
           lang="{{ $link['slug'] }}"
           aria-label="{{ $link['name'] }}"
           @if($link['active']) aria-current="page" @endif>
            <img src="{{ \App\Services\FlagService::url($link['slug']) }}"
                 alt="{{ $link['name'] }}"
                 width="44"
                 height="44"
                 loading="eager">
        </a>
        @endforeach
    </div>
    @if($phone || $email)
    <div class="crisp-nav-mobile-flags-left">
        @if($phone)
        <span class="crisp-nav-mobile-flags-phone">{!! cal_phone_icon() !!}{{ $phone }}</span>
        @endif
        @if($email)
        <span class="crisp-nav-mobile-flags-email">{!! cal_email_icon() !!}{{ $email }}</span>
        @endif
    </div>
    @endif
</div>
@endif