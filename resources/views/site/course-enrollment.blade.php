@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-course-enrollment-template.css?v=20260706">
@include('partials.booking-styles')
<style>
    /* Booking/Waitlist form enhancements */
    .cal-booking-section { margin-top: 40px; }
    .cal-booking-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.04); }
    .cal-booking-card-header { padding: 20px 28px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; }
    .cal-booking-card-header h3 { margin: 0; font-size: 20px; color: #1a1a2e; }
    .cal-status-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; }
    .cal-status-chip.available { background: #ecfdf5; color: #065f46; }
    .cal-status-chip.full { background: #fef2f2; color: #991b1b; }
    .cal-status-chip .dot { width: 8px; height: 8px; border-radius: 50%; }
    .cal-status-chip.available .dot { background: #10b981; }
    .cal-status-chip.full .dot { background: #ef4444; }
    .cal-booking-card-body { padding: 28px; }
    .cal-booking-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .cal-booking-grid .full { grid-column: 1 / -1; }
    .cal-booking-grid label { display: block; font-weight: 600; color: #333; margin-bottom: 5px; font-size: 14px; }
    .cal-booking-grid input, .cal-booking-grid select, .cal-booking-grid textarea {
        width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 15px; box-sizing: border-box; transition: border-color .2s, box-shadow .2s;
    }
    .cal-booking-grid input:focus, .cal-booking-grid select:focus, .cal-booking-grid textarea:focus {
        outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1);
    }
    .cal-booking-grid .section-divider { grid-column: 1 / -1; font-size: 16px; font-weight: 700; color: #1a1a2e; padding-top: 12px; border-top: 1px solid #f0f0f0; margin-top: 4px; }
    .cal-doc-upload-area { border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; transition: all .2s; }
    .cal-doc-upload-area:hover { border-color: #3b82f6; background: #f8faff; }
    .cal-doc-row { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
    .cal-doc-row select { width: 180px; flex-shrink: 0; }
    .cal-doc-row input[type=file] { flex: 1; }
    .cal-doc-row .remove-doc { background: none; border: none; color: #ef4444; cursor: pointer; font-size: 18px; padding: 4px; }
    .cal-add-doc-btn { background: none; border: 1px dashed #999; color: #666; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; margin-top: 4px; transition: all .2s; }
    .cal-add-doc-btn:hover { border-color: #3b82f6; color: #3b82f6; }
    .cal-doc-help { font-size: 13px; color: #888; margin: 6px 0 14px; }
    .cal-consent-row { margin-top: 20px; }
    .cal-consent-row label { font-weight: 400 !important; display: flex; gap: 8px; align-items: flex-start; }
    .cal-consent-row input[type=checkbox] { width: auto; margin-top: 3px; }
    .cal-booking-submit { display: block; width: 100%; padding: 14px 20px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 20px; transition: all .2s; }
    .cal-booking-submit.book-now { background: #1a1a2e; color: #fff; }
    .cal-booking-submit.book-now:hover { background: #2d2d4e; }
    .cal-booking-submit.waitlist { background: #f59e0b; color: #fff; }
    .cal-booking-submit.waitlist:hover { background: #d97706; }
    .cal-waitlist-info { background: #fffbeb; border: 1px solid #fcd34d; padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; }
    .cal-waitlist-info p { margin: 0; color: #92400e; line-height: 1.6; }
    .cal-available-info { background: #ecfdf5; border: 1px solid #a7f3d0; padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; }
    .cal-available-info p { margin: 0; color: #065f46; line-height: 1.6; }
    .cal-capacity-bar { height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; margin-top: 8px; }
    .cal-capacity-bar .fill { height: 100%; border-radius: 3px; transition: width .5s ease; }
    .cal-capacity-bar .fill.green { background: #10b981; }
    .cal-capacity-bar .fill.yellow { background: #f59e0b; }
    .cal-capacity-bar .fill.red { background: #ef4444; }
    .cal-error-box { background: #fef2f2; border: 1px solid #fca5a5; padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; color: #991b1b; }
    .cal-error-box ul { margin: 0; padding-left: 18px; }
    .cal-success-box { background: #ecfdf5; border: 1px solid #a7f3d0; padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; color: #065f46; }
    @media (max-width: 640px) {
        .cal-booking-grid { grid-template-columns: 1fr; }
        .cal-doc-row { flex-direction: column; }
        .cal-doc-row select { width: 100%; }
    }
</style>
@endpush

@push('scripts')
@include('partials.booking-scripts')
<script>
window.calCourseEnrollment = {
    pluginMissing: 'The booking form is not loaded on this page.',
    selectCourse: 'Please select at least one course you are interested in.',
    submitting: 'Submitting...',
    successMessage: 'Thank you. Your application has been received.',
    errorPrefix: 'Error:',
    tryAgain: 'Please try again.',
    networkError: 'Network error. Please try again.'
};
</script>
<script src="/js/cal-course-enrollment-template.js?v=20260707" defer></script>
<script>
function addDocumentRow() {
    const list = document.getElementById('cal-doc-list');
    if (!list) return;
    if (list.children.length >= 5) { alert('Maximum 5 documents allowed.'); return; }
    const row = document.createElement('div');
    row.className = 'cal-doc-row';
    row.innerHTML = `
        <select name="document_types[]">
            <option value="cv">CV / Resume</option>
            <option value="id_proof">ID (Passport/Licence)</option>
            <option value="certificate">Certificate</option>
            <option value="other" selected>Other</option>
        </select>
        <input type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png">
        <button type="button" class="remove-doc" onclick="this.parentElement.remove()" title="Remove">&times;</button>
    `;
    list.appendChild(row);
}
</script>
@endpush

@section('content')
@php
    $selectedCourseId = (int) request('course_id', 0);
    $selectedIntake = (string) request('intake_date', '');
    $isWaitlistMode = (bool) request('waitlist', false);
    $currentYear = (int) date('Y');
    $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    $cookeryAbilities = ['Beginner','Basic Home Cook','Confident Home Cook','Professional Cook'];
    $cookiesUrl = cal_language_page_url_by_template('template-cookies-policy.php', 'cookies-policy');
    $privacyUrl = cal_language_page_url_by_template('template-privacy-policy.php', 'privacy-policy');
    $pageContent = trim($page->post_content ?? '');

    // ── Capacity Check ──
    // Check confirmed bookings vs course capacity if a course is selected
    $slotsAvailable = true;
    $confirmedCount = 0;
    $maxCapacity = 0;
    $selectedCourseName = '';

    if ($selectedCourseId) {
        $selectedCourse = \App\Models\Post::with('meta')->find($selectedCourseId);
        $selectedCourseName = $selectedCourse?->post_title ?? '';

        // Get capacity from course meta or intake_dates table
        $intake = null;
        if ($selectedIntake) {
            $intake = \App\Models\IntakeDate::where('course_id', $selectedCourseId)
                ->whereDate('intake_date', $selectedIntake)
                ->first();
        }

        if ($intake) {
            $maxCapacity = (int) $intake->max_students;
            $confirmedCount = (int) $intake->enrolled_count + \App\Models\Booking::confirmedCount($selectedCourseId, $selectedIntake);
            $slotsAvailable = $intake->isAvailable();
        } else {
            // Fallback: check ACF field or default capacity
            $maxCapacity = (int) \App\Services\AcfFieldService::getField($selectedCourseId, 'ca_max_students', 15);
            $confirmedCount = \App\Models\Booking::confirmedCount($selectedCourseId);
            $slotsAvailable = $confirmedCount < $maxCapacity;
        }

        // Also check ACF display status if no override is set
        if (!$isWaitlistMode && (!$intake || $intake->availability_override === null)) {
            $ranges = \App\Services\CourseIntakeService::displayRanges($selectedCourseId);
            foreach ($ranges as $range) {
                if (($range['start'] ?? '') === $selectedIntake && ($range['status'] ?? '') === 'full') {
                    $slotsAvailable = false;
                    break;
                }
            }
        }
    }

    if ($isWaitlistMode) {
        $slotsAvailable = false;
    }

    $capacityPercent = $maxCapacity > 0 ? min(100, round(($confirmedCount / $maxCapacity) * 100)) : 0;
    $capacityColor = $capacityPercent < 70 ? 'green' : ($capacityPercent < 100 ? 'yellow' : 'red');
@endphp

<main id="main" class="site-main cal-application-page">
    @php cal_render_page_banner('cal-application', $page->post_title ?? 'Course Enrollment'); @endphp

    <section class="cal-application-breadcrumb-row">
        <div class="cal-application-shell">
            @php cal_render_breadcrumbs('cal-application-breadcrumbs'); @endphp
        </div>
    </section>

    <section class="cal-application-section">
        <div class="cal-application-shell">
            <div class="cal-form-page">
                <header class="cal-form-head">
                    <h2>Application Details</h2>
                </header>

                <section class="cal-form-body">
                    @if($pageContent)
                    <div class="cal-intro">
                        {!! \App\Services\CulinaAcademy::fixContentUrls($pageContent) !!}
                    </div>
                    @endif

                    {{-- ═══════════════════════════════════════════
                         BOOKING FORM (Slots Available)
                         OR WAITING LIST FORM (Course Full)
                         ═══════════════════════════════════════════ --}}

                    @if($selectedCourseId)
                    <div class="cal-booking-section">
                        <div class="cal-booking-card">
                            <div class="cal-booking-card-header">
                                <h3>
                                    @if($slotsAvailable)
                                        📋 Book Now
                                    @else
                                        📝 Join Waiting List
                                    @endif
                                    @if($selectedCourseName) — {{ $selectedCourseName }} @endif
                                </h3>
                                <span class="cal-status-chip {{ $slotsAvailable ? 'available' : 'full' }}">
                                    <span class="dot"></span>
                                    {{ $slotsAvailable ? 'Spots Available' : 'Course Full' }}
                                </span>
                            </div>

                            <div class="cal-booking-card-body">
                                @if($maxCapacity > 0)
                                <div style="font-size:13px;color:#666;margin-bottom:16px;">
                                    <strong>{{ $confirmedCount }}</strong> of <strong>{{ $maxCapacity }}</strong> spots filled
                                    <div class="cal-capacity-bar">
                                        <div class="fill {{ $capacityColor }}" style="width: {{ $capacityPercent }}%"></div>
                                    </div>
                                </div>
                                @endif

                                @if($errors->any())
                                <div class="cal-error-box">
                                    <ul>
                                        @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                @endif

                                @if(session('success'))
                                <div class="cal-success-box">{{ session('success') }}</div>
                                @endif

                                @if($slotsAvailable)
                                {{-- ── BOOK NOW FORM ─────────────────────── --}}
                                <div class="cal-available-info">
                                    <p>✅ <strong>Great news!</strong> Spots are still available. Fill in your details, upload your documents, and proceed to secure deposit payment.</p>
                                </div>

                                <form action="{{ route('booking.submit', ['locale' => request()->route('locale') ?? app()->getLocale()]) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="course_id" value="{{ $selectedCourseId }}">
                                    <input type="hidden" name="intake_date" value="{{ $selectedIntake }}">

                                    <div class="cal-booking-grid">
                                        <div>
                                            <label for="booking-first-name">First Name *</label>
                                            <input id="booking-first-name" name="first_name" type="text" required value="{{ old('first_name') }}">
                                        </div>
                                        <div>
                                            <label for="booking-last-name">Last Name *</label>
                                            <input id="booking-last-name" name="last_name" type="text" required value="{{ old('last_name') }}">
                                        </div>
                                        <div>
                                            <label for="booking-email">Email *</label>
                                            <input id="booking-email" name="email" type="email" required value="{{ old('email') }}">
                                        </div>
                                        <div>
                                            <label for="booking-phone">Phone *</label>
                                            <input id="booking-phone" name="phone" type="tel" required value="{{ old('phone') }}">
                                        </div>
                                        <div class="full">
                                            <label for="booking-address">Address</label>
                                            <input id="booking-address" name="address" type="text" value="{{ old('address') }}">
                                        </div>
                                        <div>
                                            <label for="booking-city">City</label>
                                            <input id="booking-city" name="city" type="text" value="{{ old('city') }}">
                                        </div>
                                        <div>
                                            <label for="booking-postcode">Postcode</label>
                                            <input id="booking-postcode" name="postcode" type="text" value="{{ old('postcode') }}">
                                        </div>
                                        <div>
                                            <label for="booking-country">Country</label>
                                            <input id="booking-country" name="country" type="text" value="{{ old('country') }}">
                                        </div>
                                        <div>
                                            <label for="booking-passport">Passport Country</label>
                                            <input id="booking-passport" name="passport_country" type="text" value="{{ old('passport_country') }}">
                                        </div>
                                        <div>
                                            <label>Do you require a visa?</label>
                                            <div style="display:flex;gap:16px;padding-top:6px;">
                                                <label style="font-weight:400;"><input type="radio" name="visa" value="Yes" {{ old('visa') === 'Yes' ? 'checked' : '' }}> Yes</label>
                                                <label style="font-weight:400;"><input type="radio" name="visa" value="No" {{ old('visa', 'No') === 'No' ? 'checked' : '' }}> No</label>
                                            </div>
                                        </div>

                                        <div class="full section-divider">📎 Required Documents</div>
                                        <div class="full">
                                            <p class="cal-doc-help">Upload your CV, photo ID (passport or driving licence), and any relevant certificates. <strong>Accepted formats:</strong> PDF, JPG, PNG. <strong>Max size:</strong> 5MB each.</p>
                                            <div class="cal-doc-upload-area">
                                                <div id="cal-doc-list">
                                                    <div class="cal-doc-row">
                                                        <select name="document_types[]">
                                                            <option value="cv">CV / Resume</option>
                                                            <option value="id_proof">ID (Passport/Licence)</option>
                                                            <option value="certificate">Certificate</option>
                                                            <option value="other">Other</option>
                                                        </select>
                                                        <input type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png" required>
                                                    </div>
                                                    <div class="cal-doc-row">
                                                        <select name="document_types[]">
                                                            <option value="cv">CV / Resume</option>
                                                            <option value="id_proof" selected>ID (Passport/Licence)</option>
                                                            <option value="certificate">Certificate</option>
                                                            <option value="other">Other</option>
                                                        </select>
                                                        <input type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png" required>
                                                    </div>
                                                </div>
                                                <button type="button" class="cal-add-doc-btn" onclick="addDocumentRow()">+ Add another document</button>
                                            </div>
                                        </div>

                                        <div class="full">
                                            <label for="booking-notes">Additional Notes</label>
                                            <textarea id="booking-notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                        </div>

                                        <div class="full cal-consent-row">
                                            <label>
                                                <input type="checkbox" name="consent" value="1" required>
                                                <span>I consent to The Culina Academy collecting my details and documents. My deposit payment will be processed securely via Stripe's hosted checkout. Read our <a href="{{ $cookiesUrl }}" target="_blank" rel="noopener">Cookies Policy</a> and <a href="{{ $privacyUrl }}" target="_blank" rel="noopener">Privacy Policy</a>.</span>
                                            </label>
                                        </div>

                                        <div class="full">
                                            <button type="submit" class="cal-booking-submit book-now">Proceed to Deposit Payment →</button>
                                        </div>
                                    </div>
                                </form>

                                @else
                                {{-- ── JOIN WAITING LIST FORM ────────────── --}}
                                <div class="cal-waitlist-info">
                                    <p>⚠️ <strong>This course is currently full.</strong> Join the waiting list and we'll notify you immediately when a spot becomes available. No payment or documents required at this stage.</p>
                                </div>

                                <form action="{{ route('booking.waitinglist', ['locale' => request()->route('locale') ?? app()->getLocale()]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="course_id" value="{{ $selectedCourseId }}">
                                    <input type="hidden" name="intake_date" value="{{ $selectedIntake }}">

                                    <div class="cal-booking-grid">
                                        <div>
                                            <label for="wl-first-name">First Name *</label>
                                            <input id="wl-first-name" name="first_name" type="text" required value="{{ old('first_name') }}">
                                        </div>
                                        <div>
                                            <label for="wl-last-name">Last Name *</label>
                                            <input id="wl-last-name" name="last_name" type="text" required value="{{ old('last_name') }}">
                                        </div>
                                        <div>
                                            <label for="wl-email">Email *</label>
                                            <input id="wl-email" name="email" type="email" required value="{{ old('email') }}">
                                        </div>
                                        <div>
                                            <label for="wl-phone">Phone</label>
                                            <input id="wl-phone" name="phone" type="tel" value="{{ old('phone') }}">
                                        </div>
                                        <div class="full cal-consent-row">
                                            <label>
                                                <input type="checkbox" name="consent_wl" value="1" required>
                                                <span>I consent to The Culina Academy storing my contact details for the purpose of notifying me about course availability.</span>
                                            </label>
                                        </div>
                                        <div class="full">
                                            <button type="submit" class="cal-booking-submit waitlist">📝 Join Waiting List</button>
                                        </div>
                                    </div>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- ═══════════════════════════════════════════
                         ORIGINAL APPLICATION FORM (when no specific course selected)
                         ═══════════════════════════════════════════ --}}

                    @if(!$selectedCourseId)
                    <form id="caStandaloneApplicationForm" class="cal-application-form" action="" method="post">
                        @csrf
                        <input name="course_id" type="hidden" value="{{ $selectedCourseId }}">
                        <input name="intake_date" type="hidden" value="{{ $selectedIntake }}">
                        <div class="cal-application-grid">
                            <div>
                                <label for="first_name">Name *</label>
                                <input id="first_name" name="first_name" required type="text">
                            </div>
                            <div>
                                <label for="surname">Surname *</label>
                                <input id="surname" name="surname" required type="text">
                            </div>
                            <div>
                                <label for="email">Email *</label>
                                <input id="email" name="email" required type="email">
                            </div>
                            <div>
                                <label for="phone">Phone Number *</label>
                                <div class="cal-phone-row">
                                    <input id="country" name="country" required type="hidden" value="United Kingdom">
                                    <input id="intl_code" name="intl_code" required type="hidden" value="44">
                                    <input id="phone" name="phone" required type="tel">
                                </div>
                            </div>
                            <div class="full">
                                <label for="address">Address *</label>
                                <input id="address" name="address" required type="text">
                            </div>
                            <div>
                                <label for="city">City *</label>
                                <input id="city" name="city" required type="text">
                            </div>
                            <div>
                                <label for="postcode">Postcode *</label>
                                <input id="postcode" name="postcode" required type="text">
                            </div>
                            <div>
                                <label for="passport_country">Which nationality is your passport from? *</label>
                                <input id="passport_country" name="passport_country" required type="text">
                            </div>
                            <div>
                                <label>Do you require a visa? *</label>
                                <div class="radio-list">
                                    <label><input name="visa" required type="radio" value="Yes"> Yes</label>
                                    <label><input name="visa" type="radio" value="No"> No</label>
                                </div>
                            </div>
                            <div>
                                <label for="heard">How did you hear about us? *</label>
                                <input id="heard" name="heard" required type="text">
                            </div>
                            <div>
                                <label for="start_year">Choose year *</label>
                                <select id="start_year" name="start_year" required>
                                    <option value="">Choose year</option>
                                    @for($year = $currentYear; $year <= $currentYear + 4; $year++)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label for="start_month">Choose month *</label>
                                <select id="start_month" name="start_month" required>
                                    <option value="">Choose month</option>
                                    @foreach($months as $month)
                                    <option value="{{ $month }}">{{ $month }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="section-title">Course Preferences</div>
                            <div class="full">
                                <label>Cookery Ability *</label>
                                <div class="radio-list">
                                    @foreach($cookeryAbilities as $ability)
                                    <label><input name="cookery_ability" required type="radio" value="{{ $ability }}"> {{ $ability }}</label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="full">
                                <label>Which Course/s are you interested in? *</label>
                                <div class="check-list">
                                    @foreach($courses as $course)
                                    <label>
                                        <input name="courses[]" type="checkbox" value="{{ $course->post_title }}" @checked($selectedCourseId === $course->ID)>
                                        {{ $course->post_title }}
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="full">
                                <label for="motivation">Why do you want to join this/these course/s? *</label>
                                <textarea id="motivation" name="motivation" required></textarea>
                            </div>
                            <div class="consent">
                                <label for="cal-application-consent">
                                    <input id="cal-application-consent" name="consent" required type="checkbox" value="1">
                                    <span class="consent-text">I consent to The Culina Academy collecting my details through this form. Please read our <a href="{{ $cookiesUrl }}" target="_blank" rel="noopener">Cookies Policy</a> and <a href="{{ $privacyUrl }}" target="_blank" rel="noopener">Privacy Policy</a>.</span>
                                </label>
                            </div>
                            <div class="full">
                                <div id="caStandaloneApplicationNotice" class="cal-application-notice" aria-live="polite"></div>
                                <button class="submit" type="submit">Submit Application</button>
                            </div>
                        </div>
                    </form>
                    @endif
                </section>
            </div>
        </div>
    </section>
</main>
@endsection