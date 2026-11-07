<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, Helvetica, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .email-wrap { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .email-header { background: #1a1a2e; color: #fff; padding: 24px 32px; text-align: center; }
        .email-header h1 { margin: 0; font-size: 22px; font-weight: 600; }
        .email-body { padding: 32px; color: #333; line-height: 1.7; }
        .email-body h2 { color: #1a1a2e; margin-top: 0; }
        .success-box { background: #ecfdf5; border-left: 4px solid #10b981; padding: 16px; border-radius: 4px; margin: 16px 0; }
        .detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
        .detail-label { font-weight: 600; min-width: 140px; color: #555; }
        .email-footer { padding: 20px 32px; background: #fafafa; color: #888; font-size: 13px; text-align: center; border-top: 1px solid #eee; }
    </style>
</head>
<body>
<div class="email-wrap">
    <div class="email-header">
        <h1>Culina Academy</h1>
    </div>
    <div class="email-body">
        <h2>Booking Confirmed ✓</h2>
        <p>Dear {{ $booking->first_name }},</p>

        <div class="success-box">
            <strong>Great news!</strong> Your booking has been confirmed and your deposit payment has been received successfully.
        </div>

        <p><strong>Booking Details:</strong></p>
        <div class="detail-row"><span class="detail-label">Course:</span> {{ $courseTitle ?? 'Culina Academy Course' }}</div>
        @if($booking->intake_date)
        <div class="detail-row"><span class="detail-label">Intake Date:</span> {{ $booking->intake_date }}</div>
        @endif
        <div class="detail-row"><span class="detail-label">Student:</span> {{ $booking->fullName() }}</div>
        <div class="detail-row"><span class="detail-label">Deposit Paid:</span> {{ $booking->depositFormatted() }}</div>
        <div class="detail-row"><span class="detail-label">Booking Ref:</span> #{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</div>

        <p style="margin-top: 20px;">Our admissions team will be in touch shortly with the next steps for your enrolment, including any remaining paperwork and the course start information.</p>
        <p>If you have any questions, feel free to reply to this email or contact us directly.</p>
        <p>We look forward to welcoming you!</p>
        <p>Warm regards,<br><strong>The Culina Academy</strong></p>
    </div>
    <div class="email-footer">
        &copy; {{ date('Y') }} Culina Academy. All rights reserved.
    </div>
</div>
</body>
</html>
