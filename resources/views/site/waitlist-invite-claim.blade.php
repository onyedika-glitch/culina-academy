@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="/css/cal-course-enrollment-template.css?v=20260706">
@include('partials.booking-styles')
<style>
    .cal-invite-page { max-width: 700px; margin: 0 auto; padding: 40px 24px; }
    .cal-invite-banner { background: linear-gradient(135deg, #1a1a2e, #2d2d4e); color: #fff; padding: 32px; border-radius: 12px; margin-bottom: 32px; text-align: center; }
    .cal-invite-banner h2 { margin: 0 0 8px; font-size: 26px; }
    .cal-invite-banner .course-name { font-size: 18px; opacity: .85; }
    .cal-invite-timer { background: #fffbeb; border: 1px solid #fcd34d; padding: 14px 20px; border-radius: 8px; text-align: center; margin-bottom: 24px; color: #92400e; font-weight: 500; }
    .cal-invite-form { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 32px; }
    .cal-invite-form .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .cal-invite-form .form-grid .full { grid-column: 1 / -1; }
    .cal-invite-form label { display: block; font-weight: 600; color: #333; margin-bottom: 4px; font-size: 14px; }
    .cal-invite-form input, .cal-invite-form select, .cal-invite-form textarea { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 15px; box-sizing: border-box; }
    .cal-invite-form input:focus, .cal-invite-form select:focus, .cal-invite-form textarea:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
    .cal-invite-form .file-upload { border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; text-align: center; cursor: pointer; transition: all .2s; }
    .cal-invite-form .file-upload:hover { border-color: #3b82f6; background: #f8faff; }
    .cal-invite-form .file-upload p { margin: 0; color: #666; }
    .cal-invite-form .submit-btn { display: block; width: 100%; padding: 14px; background: #1a1a2e; color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 24px; transition: background .2s; }
    .cal-invite-form .submit-btn:hover { background: #2d2d4e; }
    .cal-invite-form .consent-row { margin-top: 20px; }
    .cal-invite-form .consent-row label { font-weight: 400; display: flex; gap: 8px; align-items: flex-start; }
    .cal-invite-form .consent-row input[type=checkbox] { width: auto; margin-top: 3px; }
    .cal-invite-form .file-info { font-size: 13px; color: #888; margin-top: 6px; }
    .doc-type-row { display: flex; gap: 10px; align-items: center; margin-bottom: 8px; }
    .doc-type-row select { width: 160px; flex-shrink: 0; }
    .doc-type-row input[type=file] { flex: 1; }
    .add-doc-btn { background: none; border: 1px dashed #999; color: #666; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; margin-top: 8px; }
    .add-doc-btn:hover { border-color: #3b82f6; color: #3b82f6; }
    .pre-filled { background: #f9fafb; }
</style>
@endpush

@section('content')
<main id="main" class="site-main">
    @php cal_render_page_banner('cal-application', 'Claim Your Spot'); @endphp

    <section class="cal-application-section">
        <div class="cal-invite-page">
            <div class="cal-invite-banner">
                <h2>🎉 A Spot is Waiting for You!</h2>
                @if($course)
                <p class="course-name">{{ $course->post_title }}</p>
                @endif
            </div>

            @if($entry->invite_token_expires_at)
            <div class="cal-invite-timer">
                ⏰ This invitation expires on <strong>{{ $entry->invite_token_expires_at->format('l, j F Y \\a\\t H:i') }}</strong>.
                Please complete the form below before then.
            </div>
            @endif

            @if($errors->any())
            <div style="background:#fef2f2;border:1px solid #fca5a5;padding:14px 20px;border-radius:8px;margin-bottom:20px;color:#991b1b;">
                <ul style="margin:0;padding-left:18px;">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="cal-invite-form">
                <form action="{{ url('/waitlist/invite/' . $entry->invite_token . '/claim') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-grid">
                        <div>
                            <label>First Name</label>
                            <input type="text" value="{{ $entry->first_name }}" class="pre-filled" disabled>
                        </div>
                        <div>
                            <label>Last Name</label>
                            <input type="text" value="{{ $entry->last_name }}" class="pre-filled" disabled>
                        </div>
                        <div>
                            <label>Email</label>
                            <input type="email" value="{{ $entry->email }}" class="pre-filled" disabled>
                        </div>
                        <div>
                            <label for="invite-phone">Phone *</label>
                            <input type="tel" id="invite-phone" name="phone" value="{{ old('phone', $entry->phone) }}" required>
                        </div>
                        <div class="full">
                            <label for="invite-address">Address</label>
                            <input type="text" id="invite-address" name="address" value="{{ old('address') }}">
                        </div>
                        <div>
                            <label for="invite-city">City</label>
                            <input type="text" id="invite-city" name="city" value="{{ old('city') }}">
                        </div>
                        <div>
                            <label for="invite-postcode">Postcode</label>
                            <input type="text" id="invite-postcode" name="postcode" value="{{ old('postcode') }}">
                        </div>
                        <div>
                            <label for="invite-country">Country</label>
                            <input type="text" id="invite-country" name="country" value="{{ old('country') }}">
                        </div>
                        <div>
                            <label for="invite-passport">Passport Country</label>
                            <input type="text" id="invite-passport" name="passport_country" value="{{ old('passport_country') }}">
                        </div>
                        <div>
                            <label>Do you require a visa?</label>
                            <div style="display:flex;gap:16px;padding-top:6px;">
                                <label style="font-weight:400;"><input type="radio" name="visa" value="Yes" {{ old('visa') === 'Yes' ? 'checked' : '' }}> Yes</label>
                                <label style="font-weight:400;"><input type="radio" name="visa" value="No" {{ old('visa', 'No') === 'No' ? 'checked' : '' }}> No</label>
                            </div>
                        </div>

                        <div class="full">
                            <label>Required Documents *</label>
                            <p class="file-info">Upload your CV, ID (passport/driving licence), and any relevant certificates. Accepted formats: PDF, JPG, PNG (max 5MB each)</p>
                            <div id="invite-doc-list">
                                <div class="doc-type-row">
                                    <select name="document_types[]">
                                        <option value="cv">CV / Resume</option>
                                        <option value="id_proof">ID (Passport/Licence)</option>
                                        <option value="certificate">Certificate</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <input type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png" required>
                                </div>
                                <div class="doc-type-row">
                                    <select name="document_types[]">
                                        <option value="cv">CV / Resume</option>
                                        <option value="id_proof" selected>ID (Passport/Licence)</option>
                                        <option value="certificate">Certificate</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <input type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png" required>
                                </div>
                            </div>
                            <button type="button" class="add-doc-btn" onclick="addDocRow()">+ Add another document</button>
                        </div>

                        <div class="full">
                            <label for="invite-notes">Additional Notes</label>
                            <textarea id="invite-notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>

                        <div class="full consent-row">
                            <label>
                                <input type="checkbox" name="consent" value="1" required>
                                <span>I consent to The Culina Academy collecting my details and documents. I understand that my deposit payment will be processed via Stripe's secure hosted checkout.</span>
                            </label>
                        </div>

                        <div class="full">
                            <button type="submit" class="submit-btn">Upload Documents & Pay Deposit →</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
function addDocRow() {
    const list = document.getElementById('invite-doc-list');
    if (list.children.length >= 5) { alert('Maximum 5 documents allowed.'); return; }
    const row = document.createElement('div');
    row.className = 'doc-type-row';
    row.innerHTML = `
        <select name="document_types[]">
            <option value="cv">CV / Resume</option>
            <option value="id_proof">ID (Passport/Licence)</option>
            <option value="certificate">Certificate</option>
            <option value="other" selected>Other</option>
        </select>
        <input type="file" name="documents[]" accept=".pdf,.jpg,.jpeg,.png">
    `;
    list.appendChild(row);
}
</script>
@endpush
