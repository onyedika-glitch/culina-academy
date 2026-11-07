@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-course-enrollment-template.css?v=20260706">
<style>
    .cal-booking-result { max-width: 640px; margin: 0 auto; padding: 60px 24px; text-align: center; }
    .cal-booking-result .result-icon { width: 80px; height: 80px; margin: 0 auto 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px; }
    .cal-booking-result .result-icon.cancelled { background: #fef2f2; color: #ef4444; }
    .cal-booking-result h2 { font-size: 28px; color: #1a1a2e; margin-bottom: 12px; }
    .cal-booking-result p { color: #555; line-height: 1.7; margin: 0 0 12px; }
    .cal-booking-result .action-links { margin-top: 28px; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .cal-booking-result .action-links a { padding: 12px 28px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: all .2s; }
    .cal-booking-result .action-links .primary { background: #1a1a2e; color: #fff; }
    .cal-booking-result .action-links .primary:hover { background: #2d2d4e; }
    .cal-booking-result .action-links .secondary { background: #f5f5f5; color: #333; border: 1px solid #ddd; }
    .cal-booking-result .action-links .secondary:hover { background: #e8e8e8; }
</style>
@endpush

@section('content')
<main id="main" class="site-main">
    @php cal_render_page_banner('cal-application', 'Payment Cancelled'); @endphp

    <section class="cal-application-section">
        <div class="cal-booking-result">
            <div class="result-icon cancelled">✕</div>
            <h2>Payment Cancelled</h2>
            <p>Your payment was not completed. Your booking is still pending and no charge has been made to your card.</p>
            <p>If this was a mistake, you can try again by returning to the enrollment page. Your submitted information has been saved.</p>
            <p>If you continue to experience issues, please contact us and we'll be happy to assist.</p>

            <div class="action-links">
                <a href="{{ cal_localized_path('/course-enrollment') }}" class="primary">Try Again</a>
                <a href="{{ cal_localized_path('/contact-us') }}" class="secondary">Contact Us</a>
            </div>
        </div>
    </section>
</main>
@endsection
