@php
    $ctaTitle = cal_option('cal_footer_cta_title');
    $ctaLink = cal_option('cal_footer_cta_link');
    $ctaBackground = cal_image_url(cal_option('cal_footer_cta_background'));
    $galleryTitle = cal_option('cal_footer_gallery_heading');
    $gallery = cal_option('cal_footer_gallery');
    $aboutTitle = cal_option('cal_footer_about_heading');
    $about = cal_option('cal_footer_about');
    $contactTitle = cal_option('cal_footer_contact_heading');
    $address = cal_option('cal_footer_address');
    $phone = cal_option('cal_footer_phone');
    $email = cal_option('cal_footer_email');
    $policiesTitle = cal_option('cal_footer_policies_heading');
    $policyLinks = cal_option('cal_footer_policy_links');
    $bottomLine = cal_option('cal_footer_bottom_line');
    $ctaLink = is_array($ctaLink) ? $ctaLink : [];
    $gallery = is_array($gallery) ? $gallery : [];
    $policyLinks = is_array($policyLinks) ? $policyLinks : [];
@endphp

<div class="cal-custom-footer">
    @if($ctaTitle || !empty($ctaLink['url']) || $ctaBackground)
    <section class="cal-footer-cta">
        @if($ctaBackground)
        <img class="cal-footer-cta-bg" src="{{ $ctaBackground }}" alt="" loading="lazy">
        @endif
        <div class="cal-footer-cta-content">
            @if($ctaTitle)<h2>{{ $ctaTitle }}</h2>@endif
            @if(!empty($ctaLink['url']))
            <a href="{{ cal_localized_path(parse_url($ctaLink['url'], PHP_URL_PATH) ?: '/cooking-courses') }}" class="cal-footer-cta-button">{{ \App\Services\UiTranslationService::translateMenuTitle($ctaLink['title'] ?? 'View Our Courses') }}</a>
            @endif
        </div>
    </section>
    @endif

    <footer class="cal-footer-main">
        <div class="cal-footer-container">
            @if($galleryTitle || !empty($gallery))
            <div class="cal-footer-column">
                @if($galleryTitle)<h4>{{ $galleryTitle }}</h4>@endif
                @if(!empty($gallery))
                <div class="cal-gallery-slider" data-total="{{ count($gallery) }}" data-visible="3">
                    <div class="cal-gallery-track">
                        @foreach($gallery as $galleryItem)
                        @php
                            $image = $galleryItem['image'] ?? $galleryItem;
                            $imageUrl = cal_image_url($image);
                            $imageAlt = is_array($image) && !empty($image['alt']) ? $image['alt'] : '';
                        @endphp
                        @if($imageUrl)
                        <div class="cal-gallery-slide">
                            <a href="{{ $imageUrl }}" class="cal-gallery-lightbox-link" aria-label="{{ $imageAlt ?: 'Open gallery image' }}">
                                <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" width="89" height="89" loading="lazy" decoding="async">
                            </a>
                        </div>
                        @endif
                        @endforeach
                    </div>
                    <button class="cal-gallery-prev" type="button" aria-label="Previous"></button>
                    <button class="cal-gallery-next" type="button" aria-label="Next"></button>
                </div>
                @endif
            </div>
            @endif

            @if($aboutTitle || $about)
            <div class="cal-footer-column">
                @if($aboutTitle)<h4>{{ $aboutTitle }}</h4>@endif
                @if($about)<p>{{ $about }}</p>@endif
            </div>
            @endif

            @if($contactTitle || $address || $phone || $email)
            <div class="cal-footer-column">
                @if($contactTitle)<h4>{{ $contactTitle }}</h4>@endif
                @if($address)
                <div class="cal-contact-item"><a href="{{ cal_footer_map_url($address) }}" target="_blank" rel="noopener noreferrer">{{ $address }}</a></div>
                @endif
                @if($phone)<div class="cal-contact-item">{{ $phone }}</div>@endif
                @if($email)<div class="cal-contact-item">{{ $email }}</div>@endif
            </div>
            @endif

            @if($policiesTitle || !empty($policyLinks))
            <div class="cal-footer-column">
                @if($policiesTitle)<h4>{{ $policiesTitle }}</h4>@endif
                @if(!empty($policyLinks))
                <ul class="cal-footer-links">
                    @foreach($policyLinks as $policyLink)
                    @php $link = $policyLink['link'] ?? $policyLink; @endphp
                    @if(!empty($link['url']))
                    <li><a href="{{ cal_localized_path(parse_url($link['url'], PHP_URL_PATH) ?: '/') }}">{{ cal_i18n_text($link['title'] ?? '') }}</a></li>
                    @endif
                    @endforeach
                </ul>
                @endif
            </div>
            @endif
        </div>
        @if($bottomLine)
        <div class="cal-footer-bottom-line">{{ $bottomLine }}</div>
        @endif
    </footer>

    <div class="cal-gallery-lightbox" aria-hidden="true">
        <button class="cal-gallery-lightbox-close" type="button" aria-label="Close"></button>
        <button class="cal-gallery-lightbox-prev" type="button" aria-label="Previous image"></button>
        <div class="cal-gallery-lightbox-image-wrap"><img src="" alt=""></div>
        <button class="cal-gallery-lightbox-next" type="button" aria-label="Next image"></button>
    </div>
</div>