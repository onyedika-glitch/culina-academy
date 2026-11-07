@extends('admin.layout')

@section('title', 'Booking #' . str_pad($booking->id, 6, '0', STR_PAD_LEFT))
@section('heading', 'Booking Details')

@section('content')
<div class="admin-grid-2">
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Student Information</h3>
            @php
                $badgeClass = match($booking->status) {
                    'confirmed' => 'admin-badge-publish',
                    'pending' => 'admin-badge-pending',
                    'cancelled', 'expired' => 'admin-badge-draft',
                    default => 'admin-badge-pending',
                };
            @endphp
            <span class="admin-badge {{ $badgeClass }}" style="font-size:14px;">{{ ucfirst($booking->status) }}</span>
        </div>
        <table class="admin-table">
            <tbody>
                <tr><td style="width:140px;font-weight:600;">Booking Ref</td><td>#{{ str_pad($booking->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
                <tr><td style="font-weight:600;">Name</td><td>{{ $booking->fullName() }}</td></tr>
                <tr><td style="font-weight:600;">Email</td><td><a href="mailto:{{ $booking->email }}">{{ $booking->email }}</a></td></tr>
                <tr><td style="font-weight:600;">Phone</td><td>{{ $booking->phone ?? '—' }}</td></tr>
                <tr><td style="font-weight:600;">Address</td><td>{{ $booking->address ?? '—' }}</td></tr>
                <tr><td style="font-weight:600;">City</td><td>{{ $booking->city ?? '—' }}</td></tr>
                <tr><td style="font-weight:600;">Postcode</td><td>{{ $booking->postcode ?? '—' }}</td></tr>
                <tr><td style="font-weight:600;">Country</td><td>{{ $booking->country ?? '—' }}</td></tr>
                <tr><td style="font-weight:600;">Passport</td><td>{{ $booking->passport_country ?? '—' }}</td></tr>
                <tr><td style="font-weight:600;">Visa Required</td><td>{{ $booking->visa ?? '—' }}</td></tr>
                <tr><td style="font-weight:600;">Notes</td><td>{{ $booking->notes ?? '—' }}</td></tr>
                <tr><td style="font-weight:600;">Submitted</td><td>{{ $booking->created_at->format('M j, Y H:i:s') }}</td></tr>
            </tbody>
        </table>
    </div>

    <div>
        <div class="admin-card">
            <div class="admin-card-header">
                <h3>Course & Payment</h3>
            </div>
            <table class="admin-table">
                <tbody>
                    <tr><td style="width:140px;font-weight:600;">Course</td><td>{{ $courses[$booking->course_id] ?? 'Unknown (#' . $booking->course_id . ')' }}</td></tr>
                    <tr><td style="font-weight:600;">Intake</td><td>{{ $booking->intake_date ?? '—' }}</td></tr>
                    <tr><td style="font-weight:600;">Deposit</td><td>{{ $booking->depositFormatted() }}</td></tr>
                    <tr><td style="font-weight:600;">Currency</td><td>{{ strtoupper($booking->currency ?? 'gbp') }}</td></tr>
                    <tr><td style="font-weight:600;">Stripe Session</td><td><code class="admin-code" style="font-size:11px;word-break:break-all;">{{ $booking->stripe_checkout_session_id ?? '—' }}</code></td></tr>
                    <tr><td style="font-weight:600;">Payment Intent</td><td><code class="admin-code" style="font-size:11px;word-break:break-all;">{{ $booking->stripe_payment_intent_id ?? '—' }}</code></td></tr>
                </tbody>
            </table>
        </div>

        <div class="admin-card" style="margin-top:16px;">
            <div class="admin-card-header">
                <h3>Update Status</h3>
            </div>
            <form action="{{ route('admin.submissions.bookings.status', $booking->id) }}" method="POST" style="display:flex;gap:8px;align-items:center;">
                @csrf @method('PUT')
                <select name="status" class="admin-input" style="flex:1;">
                    @foreach(['pending', 'confirmed', 'cancelled', 'expired'] as $s)
                    <option value="{{ $s }}" @selected($booking->status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">Update</button>
            </form>
        </div>
    </div>
</div>

{{-- Documents --}}
<div class="admin-card" style="margin-top:16px;">
    <div class="admin-card-header">
        <h3>Uploaded Documents ({{ $booking->documents->count() }})</h3>
    </div>
    @if($booking->documents->isEmpty())
        <p class="admin-muted">No documents uploaded.</p>
    @else
        <table class="admin-table">
            <thead><tr><th>Type</th><th>Original Name</th><th>MIME</th><th>Size</th><th>Cloudinary ID</th></tr></thead>
            <tbody>
            @foreach($booking->documents as $doc)
                <tr>
                    <td><span class="admin-badge admin-badge-pending">{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</span></td>
                    <td>{{ $doc->original_name }}</td>
                    <td class="admin-muted">{{ $doc->mime_type }}</td>
                    <td class="admin-muted">{{ number_format($doc->size_bytes / 1024, 1) }} KB</td>
                    <td><code class="admin-code" style="font-size:11px;word-break:break-all;">{{ $doc->cloudinary_public_id ?? '—' }}</code></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>

<div style="margin-top:20px;display:flex;gap:12px;">
    <a href="{{ route('admin.submissions.index', ['tab' => 'bookings']) }}" class="admin-btn admin-btn-secondary">← Back to Bookings</a>
    <form action="{{ route('admin.submissions.bookings.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this booking?')">
        @csrf @method('DELETE')
        <button type="submit" class="admin-btn admin-btn-danger">Delete Booking</button>
    </form>
</div>
@endsection
