@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/css/intlTelInput.css">
<link rel="stylesheet" href="/css/cal-contact-us.css?v=20260715">
@endpush

@push('scripts')
<script>
window.calContact = {
    ajaxurl: '{{ cal_localized_path('/contact-us') }}',
    nonce: '{{ csrf_token() }}',
    language: document.documentElement.lang || 'en-GB',
    sendingText: 'Sending...',
    submitText: 'Submit now',
    networkError: 'Network error. Please try again.'
};
</script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.5.0/build/js/intlTelInput.min.js" defer></script>
<script src="/js/cal-contact-us.js?v=20260715" defer></script>
@endpush

@section('content')
@php
    $heroTitle = cal_page_field('cal_contact_hero_title', $page->post_title ?? 'Contact Us') ?: 'Contact Us';
    $address = cal_page_field('cal_contact_address');
    $phone = cal_page_field('cal_contact_phone');
    $email = cal_page_field('cal_contact_email');
    $hours = cal_page_field('cal_contact_hours');
    $formIntro = cal_page_field('cal_contact_form_intro');
    $mapEmbed = cal_page_field('cal_contact_map_embed');
@endphp

<main id="main" class="site-main cal-contact-page">
    @php cal_render_page_banner('cal-contact', $heroTitle); @endphp

    <section class="cal-contact-sub-bar">
        <div class="cal-contact-shell">
            @php cal_render_breadcrumbs('cal-contact-breadcrumbs'); @endphp
        </div>
    </section>

    <section class="cal-contact-info">
        <div class="cal-contact-shell">
            <div class="cal-contact-grid">
                <div class="cal-contact-card">
                    <div class="cal-contact-icon cal-icon-location">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <h3>Address</h3>
                    <p>{!! nl2br(e($address)) !!}</p>
                </div>
                <div class="cal-contact-card">
                    <div class="cal-contact-icon cal-icon-phone">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </div>
                    <h3>Phone</h3>
                    <p><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></p>
                </div>
                <div class="cal-contact-card">
                    <div class="cal-contact-icon cal-icon-email">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <h3>Email</h3>
                    <p><a href="mailto:{{ $email }}">{{ $email }}</a></p>
                    @if($hours)
                    <p><strong>Opening Hours:</strong><br>{!! nl2br(e($hours)) !!}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="cal-contact-form-section">
        <div class="cal-contact-shell cal-contact-form-shell">
            @if($formIntro)
            <p class="cal-contact-form-intro">{{ $formIntro }}</p>
            @endif
            <form class="cal-contact-form" id="calContactForm" method="post" enctype="multipart/form-data">
                @csrf
                <label for="cal-contact-name">{{ cal_i18n_text('Your Name') }} *</label>
                <input id="cal-contact-name" type="text" name="cal_name" autocomplete="name" required>
                <label for="cal-contact-email">Your Email *</label>
                <input id="cal-contact-email" type="email" name="cal_email" autocomplete="email" required>
                <div class="ca-phone-row">
                    <label for="cal-contact-phone">{{ cal_i18n_text('Your Phone') }}</label>
                    <input type="hidden" class="ca-intl-code" name="country_code" value="44">
                    <input id="cal-contact-phone" type="tel" name="cal_phone" autocomplete="tel">
                </div>
                <label for="cal-contact-message">Your Message *</label>
                <textarea id="cal-contact-message" name="cal_message" required></textarea>
                <label for="cal-contact-attachment">Attachment (optional — PDF, JPG or PNG, max 10MB)</label>
                <input id="cal-contact-attachment" type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png">
                <button type="submit" class="cal-contact-submit">Submit now</button>
                <div class="cal-contact-note" role="status" aria-live="polite"></div>
            </form>
        </div>
    </section>

    @if($mapEmbed)
    <section class="cal-contact-map">
        <div class="cal-contact-map-inner">
            <iframe class="cal-contact-map-frame" src="{{ $mapEmbed }}" width="100%" height="450" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </section>
    @endif
</main>
@endsection