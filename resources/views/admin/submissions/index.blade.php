@extends('admin.layout')

@section('title', 'Submissions')
@section('heading', 'Form Submissions')

@section('content')
<div class="admin-tab-buttons">
    <a href="{{ route('admin.submissions.index', ['tab' => 'contacts']) }}" class="admin-btn admin-btn-sm {{ $tab === 'contacts' ? 'admin-btn-primary' : 'admin-btn-secondary' }}">Contact ({{ $contacts->total() }})</a>
    <a href="{{ route('admin.submissions.index', ['tab' => 'applications']) }}" class="admin-btn admin-btn-sm {{ $tab === 'applications' ? 'admin-btn-primary' : 'admin-btn-secondary' }}">Applications ({{ $applications->total() }})</a>
    <a href="{{ route('admin.submissions.index', ['tab' => 'bookings']) }}" class="admin-btn admin-btn-sm {{ $tab === 'bookings' ? 'admin-btn-primary' : 'admin-btn-secondary' }}">Bookings ({{ $bookings->total() }})</a>
    <a href="{{ route('admin.submissions.index', ['tab' => 'waitinglists']) }}" class="admin-btn admin-btn-sm {{ $tab === 'waitinglists' ? 'admin-btn-primary' : 'admin-btn-secondary' }}">Waiting Lists ({{ $waitingLists->total() }})</a>
    <a href="{{ route('admin.submissions.index', ['tab' => 'waitlist']) }}" class="admin-btn admin-btn-sm {{ $tab === 'waitlist' ? 'admin-btn-primary' : 'admin-btn-secondary' }}">Legacy Waitlist ({{ $waitlist->total() }})</a>
    <a href="{{ route('admin.submissions.index', ['tab' => 'enrollments']) }}" class="admin-btn admin-btn-sm {{ $tab === 'enrollments' ? 'admin-btn-primary' : 'admin-btn-secondary' }}">Enrollments ({{ $enrollments->total() }})</a>
</div>

@if($tab === 'contacts')
<div class="admin-card" style="padding:0;overflow:hidden">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Email</th><th>Date</th><th></th></tr></thead>
        <tbody>
        @forelse($contacts as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ $item->email }}</td>
                <td class="admin-muted">{{ $item->created_at->format('M j, Y H:i') }}</td>
                <td><a href="{{ route('admin.submissions.contacts.show', $item->id) }}" class="admin-btn admin-btn-secondary admin-btn-sm">View</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="admin-muted">No submissions.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $contacts->appends(['tab' => 'contacts'])->links() }}

@elseif($tab === 'applications')
<div class="admin-card" style="padding:0;overflow:hidden">
    <table class="admin-table">
        <thead><tr><th>Applicant</th><th>Email</th><th>Status</th><th>Date</th><th></th></tr></thead>
        <tbody>
        @forelse($applications as $item)
            <tr>
                <td>{{ $item->first_name }} {{ $item->surname }}</td>
                <td>{{ $item->email }}</td>
                <td><span class="admin-badge admin-badge-{{ $item->status === 'pending' ? 'pending' : 'publish' }}">{{ $item->status }}</span></td>
                <td class="admin-muted">{{ $item->created_at->format('M j, Y') }}</td>
                <td><a href="{{ route('admin.submissions.applications.show', $item->id) }}" class="admin-btn admin-btn-secondary admin-btn-sm">View</a></td>
            </tr>
        @empty
            <tr><td colspan="5" class="admin-muted">No applications.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $applications->appends(['tab' => 'applications'])->links() }}

@elseif($tab === 'bookings')
{{-- ═══════════════════════════════════════════ --}}
{{--  BOOKINGS TAB                               --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="admin-card" style="padding:0;overflow:hidden">
    <table class="admin-table">
        <thead><tr><th>Student</th><th>Email</th><th>Course</th><th>Status</th><th>Deposit</th><th>Date</th><th></th></tr></thead>
        <tbody>
        @forelse($bookings as $item)
            <tr>
                <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $courses[$item->course_id] ?? $item->course_id }}</td>
                <td>
                    @php
                        $badgeClass = match($item->status) {
                            'confirmed' => 'admin-badge-publish',
                            'pending' => 'admin-badge-pending',
                            'cancelled' => 'admin-badge-draft',
                            'expired' => 'admin-badge-draft',
                            default => 'admin-badge-pending',
                        };
                    @endphp
                    <span class="admin-badge {{ $badgeClass }}">{{ ucfirst($item->status) }}</span>
                </td>
                <td>{{ $item->depositFormatted() }}</td>
                <td class="admin-muted">{{ $item->created_at->format('M j, Y H:i') }}</td>
                <td>
                    <a href="{{ route('admin.submissions.bookings.show', $item->id) }}" class="admin-btn admin-btn-secondary admin-btn-sm">View</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="admin-muted">No bookings yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $bookings->appends(['tab' => 'bookings'])->links() }}

@elseif($tab === 'waitinglists')
{{-- ═══════════════════════════════════════════ --}}
{{--  WAITING LISTS TAB (with Invite button)     --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="admin-card" style="padding:12px 16px;margin-bottom:12px;background:#fffbeb;border:1px solid #fcd34d">
    <p style="margin:0;color:#92400e;font-size:14px;">📋 Students are ranked chronologically. Click <strong>Invite</strong> to send them a secure link (24-48hr window) to upload documents and pay their deposit.</p>
</div>
<div class="admin-card" style="padding:0;overflow:hidden">
    <table class="admin-table">
        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Course</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
        @forelse($waitingLists as $index => $item)
            <tr>
                <td class="admin-muted">{{ $waitingLists->firstItem() + $index }}</td>
                <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->phone ?? '—' }}</td>
                <td>{{ $courses[$item->course_id] ?? $item->course_id }}</td>
                <td>
                    @php
                        $wlBadge = match($item->status) {
                            'waiting' => 'admin-badge-pending',
                            'invited' => 'admin-badge-publish',
                            'claimed' => 'admin-badge-publish',
                            'expired' => 'admin-badge-draft',
                            default => 'admin-badge-pending',
                        };
                    @endphp
                    <span class="admin-badge {{ $wlBadge }}">{{ ucfirst($item->status) }}</span>
                    @if($item->status === 'invited' && $item->invite_token_expires_at)
                        <br><small class="admin-muted">Expires: {{ $item->invite_token_expires_at->format('M j, H:i') }}</small>
                    @endif
                </td>
                <td class="admin-muted">{{ $item->created_at?->format('M j, Y H:i') }}</td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        @if($item->status === 'waiting')
                        <form action="{{ route('admin.submissions.waitinglists.invite', $item->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Send invitation email to {{ $item->email }}?')">
                            @csrf
                            <input type="hidden" name="expiry_hours" value="48">
                            <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm" title="Send invite email with 48hr window">✉ Invite</button>
                        </form>
                        @endif
                        <form action="{{ route('admin.submissions.waitinglists.destroy', $item->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this entry?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="admin-muted">No waiting list entries.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $waitingLists->appends(['tab' => 'waitinglists'])->links() }}

@elseif($tab === 'waitlist')
{{-- Legacy waitlist (from old ca_waitlist table) --}}
<div class="admin-card" style="padding:0;overflow:hidden">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Email</th><th>Course</th><th>Intake</th><th>Date</th></tr></thead>
        <tbody>
        @forelse($waitlist as $item)
            <tr>
                <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $courses[$item->course_id] ?? $item->course_id }}</td>
                <td>{{ $item->intake_date }}</td>
                <td class="admin-muted">{{ $item->created_at?->format('M j, Y') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="admin-muted">No waitlist entries.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $waitlist->appends(['tab' => 'waitlist'])->links() }}

@else
<div class="admin-card" style="padding:0;overflow:hidden">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        @forelse($enrollments as $item)
            <tr>
                <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->status }}</td>
                <td class="admin-muted">{{ $item->created_at?->format('M j, Y') }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="admin-muted">No enrollments.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
{{ $enrollments->appends(['tab' => 'enrollments'])->links() }}
@endif
@endsection