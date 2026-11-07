@extends('admin.layout')

@section('title', 'Contact Submission')
@section('heading', 'Contact: ' . $submission->name)

@section('content')
<div class="admin-card">
    <p><strong>Email:</strong> <a href="mailto:{{ $submission->email }}">{{ $submission->email }}</a></p>
    @if($submission->phone)<p><strong>Phone:</strong> {{ $submission->phone }}</p>@endif
    <p><strong>Submitted:</strong> {{ $submission->created_at->format('M j, Y H:i') }}</p>
    @if($submission->locale)<p><strong>Locale:</strong> {{ $submission->locale }}</p>@endif
    @if($submission->page_url)<p><strong>Page:</strong> <a href="{{ $submission->page_url }}" target="_blank">{{ $submission->page_url }}</a></p>@endif
    <hr style="border:none;border-top:1px solid var(--admin-border);margin:1rem 0">
    <p style="white-space:pre-wrap">{{ $submission->message }}</p>
</div>
<div class="admin-actions">
    <a href="{{ route('admin.submissions.index', ['tab' => 'contacts']) }}" class="admin-btn admin-btn-secondary">Back</a>
    <form method="POST" action="{{ route('admin.submissions.contacts.destroy', $submission->id) }}" onsubmit="return confirm('Delete this submission?')">
        @csrf @method('DELETE')
        <button type="submit" class="admin-btn admin-btn-danger">Delete</button>
    </form>
</div>
@endsection