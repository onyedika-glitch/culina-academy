@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-course-enrollment-template.css?v=20260706">
<style>
    .cal-booking-result { max-width: 640px; margin: 0 auto; padding: 60px 24px; text-align: center; }
    .cal-booking-result .result-icon { width: 80px; height: 80px; margin: 0 auto 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px; }
    .cal-booking-result .result-icon.success { background: #ecfdf5; color: #10b981; }
    .cal-booking-result h2 { font-size: 28px; color: #1a1a2e; margin-bottom: 12px; }
    .cal-booking-result p { color: #555; line-height: 1.7; margin: 0 0 12px; }
    .cal-booking-result .ref-box { background: #f0f4ff; border: 1px solid #dbeafe; padding: 16px 24px; border-radius: 8px; margin: 24px 0; display: inline-block; }
    .cal-booking-result .ref-box strong { color: #1a1a2e; }
    .cal-booking-result .back-link { display: inline-block; margin-top: 24px; padding: 12px 28px; background: #1a1a2e; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600; transition: background .2s; }
    .cal-booking-result .back-link:hover { background: #2d2d4e; }
</style>
@endpush

@section('content')
<main id="main" class="site-main">
    @php cal_render_page_banner('cal-application', 'Booking Confirmation'); @endphp

    <section class="cal-application-section">
        <div class="cal-booking-result">
            <div class="result-icon success">✓</div>
            <h2>Payment Received!</h2>
            <p>Thank you for your booking. Your deposit payment has been received and your place is being confirmed.</p>

            @if($booking)
            <div class="ref-box">
                <strong>Booking Reference:</strong> #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}<br>
                <strong>Status:</strong> {{ ucfirst($booking->status) }}
            </div>
            @endif

            <p>You will receive a confirmation email shortly with your booking details and next steps.</p>
            <p style="color: #888; font-size: 14px;">If you don't receive an email within 10 minutes, please check your spam folder or contact us.</p>

            <a href="{{ cal_localized_path('/courses') }}" class="back-link">Back to Courses</a>
        </div>
    </section>
</main>
@endsection
