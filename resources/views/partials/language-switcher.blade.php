@php
    $links = \App\Services\PolylangService::getLanguageSwitcherLinks();
@endphp

@if(count($links) > 1)
<ul class="crisp-nav-lang-switcher" aria-label="{{ cal_i18n_text('Language switcher') }}">
    @foreach($links as $link)
    <li>
        <a href="{{ $link['url'] }}"
           hreflang="{{ $link['slug'] }}"
           lang="{{ $link['slug'] }}"
           class="{{ $link['active'] ? 'active is-active' : '' }}"
           aria-label="{{ $link['name'] }}"
           @if($link['active']) aria-current="page" @endif>
            <img src="{{ \App\Services\FlagService::url($link['slug']) }}"
                 alt="{{ $link['name'] }}"
                 width="28"
                 height="28"
                 loading="lazy">
        </a>
    </li>
    @endforeach
</ul>
@endif