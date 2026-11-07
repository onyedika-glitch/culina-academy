@php
    $name = $restaurant['name'] ?? '';
    $image = $restaurant['image'] ?? '';
    $address = $restaurant['address'] ?? '';
    $michelin = (int) ($restaurant['michelin'] ?? 0);
    $aa = (int) ($restaurant['aa'] ?? 0);
    $mapUrl = $restaurant['map_url'] ?? '';
    $websiteUrl = $restaurant['website_url'] ?? '';
    $websiteLabel = $restaurant['website_label'] ?? $websiteUrl;
    $michelinIcon = \App\Services\RestaurantPartnersService::awardIconUrl('michelin');
    $aaIcon = \App\Services\RestaurantPartnersService::awardIconUrl('aa');
@endphp

<article class="cal-rp-card">
    <header class="cal-rp-card-header">
        <h2 class="cal-rp-card-title">{{ $name }}</h2>
    </header>
    <div class="cal-rp-card-body">
        @if($image)
        <div class="cal-rp-card-media">
            <img src="{{ $image }}" alt="{{ $name }}" loading="lazy" decoding="async">
        </div>
        @endif
        <div class="cal-rp-card-details">
            @if($address)
            <p class="cal-rp-address">{!! nl2br(e($address)) !!}</p>
            @endif
            @if($michelin > 0 && $michelinIcon)
            <p class="cal-rp-awards">
                <span class="cal-rp-awards-label">Michelin Stars:</span>
                <span class="cal-rp-awards-icons">
                    @for($i = 0; $i < $michelin; $i++)
                    <img src="{{ $michelinIcon }}" alt="" width="15" height="15" loading="lazy" decoding="async" aria-hidden="true">
                    @endfor
                </span>
            </p>
            @endif
            @if($aa > 0 && $aaIcon)
            <p class="cal-rp-awards">
                <span class="cal-rp-awards-label">AA Rosettes:</span>
                <span class="cal-rp-awards-icons">
                    @for($i = 0; $i < $aa; $i++)
                    <img src="{{ $aaIcon }}" alt="" width="15" height="15" loading="lazy" decoding="async" aria-hidden="true">
                    @endfor
                </span>
            </p>
            @endif
            @if($mapUrl)
            <p class="cal-rp-map-link">
                <a href="{{ $mapUrl }}" target="_blank" rel="noopener noreferrer">View map</a>
            </p>
            @endif
        </div>
        @if($websiteUrl)
        <p class="cal-rp-website">
            <a href="{{ $websiteUrl }}" target="_blank" rel="noopener noreferrer">{{ $websiteLabel }}</a>
        </p>
        @endif
    </div>
</article>