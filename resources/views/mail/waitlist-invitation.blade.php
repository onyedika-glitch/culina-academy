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
        .urgent-box { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 4px; margin: 16px 0; }
        .cta-btn { display: inline-block; background: #1a1a2e; color: #fff !important; text-decoration: none; padding: 14px 32px; border-radius: 6px; font-size: 16px; font-weight: 600; margin: 16px 0; }
        .email-footer { padding: 20px 32px; background: #fafafa; color: #888; font-size: 13px; text-align: center; border-top: 1px solid #eee; }
    </style>
</head>
<body>
<div class="email-wrap">
    <div class="email-header">
        <h1>Culina Academy</h1>
    </div>
    <div class="email-body">
        <h2>A Spot Has Opened Up! 🎉</h2>
        <p>Dear {{ $entry->first_name }},</p>
        <p>Great news — a place has become available on the course you were waiting for:</p>

        @if($courseTitle)
        <p style="font-size: 18px; font-weight: 600; color: #1a1a2e;">{{ $courseTitle }}</p>
        @endif

        <div class="urgent-box">
            <strong>⏰ Time-Sensitive:</strong> You have <strong>{{ $expiryHours }} hours</strong> to claim this spot. After that, the invitation will expire and the spot will be offered to the next person on the waiting list.
        </div>

        <p>To claim your spot, you'll need to:</p>
        <ol>
            <li>Upload your required documents (CV, ID, etc.)</li>
            <li>Pay your deposit</li>
        </ol>

        <p style="text-align: center;">
            <a href="{{ $inviteUrl }}" class="cta-btn">Claim Your Spot Now →</a>
        </p>

        <p style="font-size: 13px; color: #888;">If the button doesn't work, copy and paste this link into your browser:<br>
        <a href="{{ $inviteUrl }}">{{ $inviteUrl }}</a></p>

        <p>If you have any questions, please don't hesitate to contact us immediately.</p>
        <p>Warm regards,<br><strong>The Culina Academy</strong></p>
    </div>
    <div class="email-footer">
        &copy; {{ date('Y') }} Culina Academy. All rights reserved.
    </div>
</div>
</body>
</html>
