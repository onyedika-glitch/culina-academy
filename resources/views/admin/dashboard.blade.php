@extends('admin.layout')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="admin-stats">
    <div class="admin-stat"><div class="value">{{ $stats['pages'] }}</div><div class="label">Pages</div></div>
    <div class="admin-stat"><div class="value">{{ $stats['courses'] }}</div><div class="label">Courses</div></div>
    <div class="admin-stat"><div class="value">{{ $stats['posts'] }}</div><div class="label">Blog Posts</div></div>
    <div class="admin-stat"><div class="value">{{ $stats['bookings_confirmed'] }}</div><div class="label">Confirmed Bookings</div></div>
    <div class="admin-stat"><div class="value">{{ $stats['bookings_pending'] }}</div><div class="label">Pending Bookings</div></div>
    <div class="admin-stat"><div class="value">{{ $stats['waiting_lists'] }}</div><div class="label">On Waiting List</div></div>
    <div class="admin-stat"><div class="value">{{ $stats['applications'] }}</div><div class="label">Applications</div></div>
    <div class="admin-stat"><div class="value">{{ $stats['contacts'] }}</div><div class="label">Contacts</div></div>
</div>

<div class="admin-grid-2">
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Recent Contact Messages</h3>
            <a href="{{ route('admin.submissions.index', ['tab' => 'contacts']) }}" class="admin-btn admin-btn-secondary admin-btn-sm">View all</a>
        </div>
        @if($recentContacts->isEmpty())
            <p class="admin-muted">No contact submissions yet.</p>
        @else
            <table class="admin-table">
                <thead><tr><th>Name</th><th>Date</th><th></th></tr></thead>
                <tbody>
                @foreach($recentContacts as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td class="admin-muted">{{ $item->created_at->format('M j, Y') }}</td>
                        <td class="admin-table-actions-col">
                            <a href="{{ route('admin.submissions.contacts.show', $item->id) }}" class="admin-btn admin-btn-secondary admin-btn-sm">Open</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Recent Applications</h3>
            <a href="{{ route('admin.submissions.index', ['tab' => 'applications']) }}" class="admin-btn admin-btn-secondary admin-btn-sm">View all</a>
        </div>
        @if($recentApplications->isEmpty())
            <p class="admin-muted">No applications yet.</p>
        @else
            <table class="admin-table">
                <thead><tr><th>Applicant</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @foreach($recentApplications as $item)
                    <tr>
                        <td>{{ $item->first_name }} {{ $item->surname }}</td>
                        <td><span class="admin-badge admin-badge-{{ $item->status === 'pending' ? 'pending' : 'publish' }}">{{ $item->status }}</span></td>
                        <td class="admin-table-actions-col">
                            <a href="{{ route('admin.submissions.applications.show', $item->id) }}" class="admin-btn admin-btn-secondary admin-btn-sm">Open</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>Recent Bookings</h3>
        <a href="{{ route('admin.submissions.index', ['tab' => 'bookings']) }}" class="admin-btn admin-btn-secondary admin-btn-sm">View all</a>
    </div>
    @if($recentBookings->isEmpty())
        <p class="admin-muted">No bookings yet.</p>
    @else
        <table class="admin-table">
            <thead><tr><th>Student</th><th>Course</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @foreach($recentBookings as $item)
                <tr>
                    <td>{{ $item->first_name }} {{ $item->last_name }}</td>
                    <td class="admin-muted">{{ $item->course?->post_title ?? '#' . $item->course_id }}</td>
                    <td><span class="admin-badge {{ $item->status === 'confirmed' ? 'admin-badge-publish' : 'admin-badge-pending' }}">{{ ucfirst($item->status) }}</span></td>
                    <td class="admin-table-actions-col">
                        <a href="{{ route('admin.submissions.bookings.show', $item->id) }}" class="admin-btn admin-btn-secondary admin-btn-sm">Open</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>Languages</h3>
        <a href="{{ route('admin.languages.index') }}" class="admin-btn admin-btn-secondary admin-btn-sm">Manage</a>
    </div>
    <table class="admin-table">
        <thead><tr><th>Language</th><th>Code</th><th>Locale</th></tr></thead>
        <tbody>
        @foreach($languages as $lang)
            <tr>
                <td>{{ $lang['name'] }}</td>
                <td><code class="admin-code">{{ $lang['slug'] }}</code></td>
                <td class="admin-muted">{{ $lang['locale'] }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection