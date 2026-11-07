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
        .highlight { background: #f0f4ff; border-left: 4px solid #3b82f6; padding: 16px; border-radius: 4px; margin: 16px 0; }
        .email-footer { padding: 20px 32px; background: #fafafa; color: #888; font-size: 13px; text-align: center; border-top: 1px solid #eee; }
    </style>
</head>
<body>
<div class="email-wrap">
    <div class="email-header">
        <h1>Culina Academy</h1>
    </div>
    <div class="email-body">
        <h2>You're on the Waiting List!</h2>
        <p>Dear {{ $entry->first_name }},</p>
        <p>Thank you for your interest in joining us. We've successfully added you to the waiting list.</p>

        @if($courseTitle)
        <div class="highlight">
            <strong>Course:</strong> {{ $courseTitle }}<br>
            @if($entry->intake_date)
            <strong>Preferred Intake:</strong> {{ $entry->intake_date }}
            @endif
        </div>
        @endif

        <p>If a spot becomes available, we'll send you an email with a special link that gives you a limited window (24–48 hours) to upload your documents, pay your deposit, and secure your place.</p>
        <p>Please keep an eye on your inbox (and spam folder). We'll be in touch as soon as a spot opens up.</p>
        <p>In the meantime, if you have any questions, please don't hesitate to contact us.</p>
        <p>Warm regards,<br><strong>The Culina Academy</strong></p>
    </div>
    <div class="email-footer">
        &copy; {{ date('Y') }} Culina Academy. All rights reserved.
    </div>
</div>
</body>
</html>
