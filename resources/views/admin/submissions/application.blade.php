@extends('admin.layout')

@section('title', 'Application')
@section('heading', $application->first_name . ' ' . $application->surname)

@section('content')
<div class="admin-form-grid">
    <div class="admin-card">
        <h3>Personal details</h3>
        <p><strong>Email:</strong> {{ $application->email }}</p>
        <p><strong>Phone:</strong> {{ $application->phone }}</p>
        <p><strong>Address:</strong> {{ $application->address }}, {{ $application->city }} {{ $application->postcode }}</p>
        <p><strong>Country:</strong> {{ $application->country }}</p>
        <p><strong>Passport country:</strong> {{ $application->passport_country }}</p>
        <p><strong>Visa required:</strong> {{ $application->visa }}</p>
        <p><strong>Heard about us:</strong> {{ $application->heard }}</p>
        <p><strong>Start:</strong> {{ $application->start_month }} {{ $application->start_year }}</p>
        <p><strong>Cookery ability:</strong> {{ $application->cookery_ability }}</p>
        <h3 style="margin-top:1.5rem">Motivation</h3>
        <p style="white-space:pre-wrap">{{ $application->motivation }}</p>
        @if($application->courses)
            <h3 style="margin-top:1.5rem">Selected courses</h3>
            <ul>
                @foreach($application->courses as $course)
                    <li>{{ is_array($course) ? ($course['name'] ?? json_encode($course)) : $course }}</li>
                @endforeach
            </ul>
        @endif
    </div>
    <div>
        <div class="admin-card">
            <h3>Status</h3>
            <form method="POST" action="{{ route('admin.submissions.applications.status', $application->id) }}">
                @csrf @method('PUT')
                <div class="admin-field">
                    <select name="status">
                        @foreach(['pending','reviewed','accepted','rejected'] as $status)
                            <option value="{{ $status }}" @selected($application->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">Update status</button>
            </form>
            <p class="admin-muted" style="margin-top:1rem">Submitted {{ $application->created_at->format('M j, Y H:i') }}</p>
        </div>
        <div class="admin-actions" style="margin-top:1rem">
            <a href="{{ route('admin.submissions.index', ['tab' => 'applications']) }}" class="admin-btn admin-btn-secondary">Back</a>
            <form method="POST" action="{{ route('admin.submissions.applications.destroy', $application->id) }}" onsubmit="return confirm('Delete this application?')">
                @csrf @method('DELETE')
                <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection