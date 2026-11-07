@extends('admin.layout')

@section('title', 'Media Library')
@section('heading', 'Media Library')

@section('content')
<form class="admin-filters" method="GET">
    <div class="admin-field">
        <label>Search</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Filename…">
    </div>
    <button type="submit" class="admin-btn admin-btn-secondary">Search</button>
</form>

<form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="admin-card" style="margin-bottom:1rem">
    @csrf
    <div class="admin-field">
        <label>Upload new file</label>
        <input type="file" name="file" accept="image/*,.pdf" required>
    </div>
    <button type="submit" class="admin-btn admin-btn-primary">Upload</button>
</form>

<div class="admin-media-grid">
    @forelse($media as $item)
        @php $formatted = \App\Services\MediaAdminService::formatAttachment($item); @endphp
        <div class="admin-media-item-static">
            @if(str_starts_with($formatted['mime'], 'image/'))
                <img src="{{ $formatted['url'] }}" alt="{{ $formatted['title'] }}">
            @else
                <div class="admin-media-file">{{ $formatted['mime'] }}</div>
            @endif
            <span>{{ $formatted['title'] }}</span>
            <small class="admin-muted">ID: {{ $formatted['id'] }}</small>
            <form method="POST" action="{{ route('admin.media.destroy', $item->ID) }}" onsubmit="return confirm('Delete this file?')">
                @csrf @method('DELETE')
                <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Delete</button>
            </form>
        </div>
    @empty
        <p class="admin-muted">No media files found.</p>
    @endforelse
</div>

{{ $media->links() }}
@endsection