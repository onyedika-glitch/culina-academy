@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-course-enrollment-template.css?v=20260706">
<style>
    .cal-booking-result { max-width: 640px; margin: 0 auto; padding: 60px 24px; text-align: center; }
    .cal-booking-result .result-icon { width: 80px; height: 80px; margin: 0 auto 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px; }
    .cal-booking-result .result-icon.expired { background: #fef3c7; color: #d97706; }
    .cal-booking-result h2 { font-size: 28px; color: #1a1a2e; margin-bottom: 12px; }
    .cal-booking-result p { color: #555; line-height: 1.7; margin: 0 0 12px; }
    .cal-booking-result .back-link { display: inline-block; margin-top: 24px; padding: 12px 28px; background: #1a1a2e; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600; }
    .cal-booking-result .back-link:hover { background: #2d2d4e; }
</style>
@endpush

@section('content')
<main id="main" class="site-main">
    @php cal_render_page_banner('cal-application', 'Invitation Expired'); @endphp

    <section class="cal-application-section">
        <div class="cal-booking-result">
            <div class="result-icon expired">⏰</div>
            <h2>Invitation Expired</h2>
            <p>Unfortunately, the time window for claiming this spot has passed. The invitation link has expired.</p>
            <p>Don't worry — if you're still interested, you can join the waiting list again. We'll notify you as soon as another spot becomes available.</p>
            <a href="{{ cal_localized_path('/course-enrollment') }}" class="back-link">View Courses</a>
        </div>
    </section>
</main>
@endsection
