@extends('admin.layout')

@section('title', $label . 's')
@section('heading', $label . 's')

@section('content')
<div class="admin-page-header">
    <p class="admin-page-subtitle">Manage {{ strtolower($label) }} content, translations, and publish status.</p>
    <a href="{{ route(str_replace('.index', '.create', $indexRoute)) }}" class="admin-btn admin-btn-primary">+ New {{ $label }}</a>
</div>

<form class="admin-filters admin-card" method="GET">
    <div class="admin-field">
        <label>Search</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Title or slug…">
    </div>
    <div class="admin-field">
        <label>Status</label>
        <select name="status">
            <option value="">All</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
    </div>
    <div class="admin-field">
        <label>Language</label>
        <select name="lang">
            <option value="">All</option>
            @foreach($languages as $lang)
                <option value="{{ $lang['slug'] }}" @selected(request('lang') === $lang['slug'])>{{ $lang['name'] }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="admin-btn admin-btn-secondary">Apply filters</button>
    @if(request()->hasAny(['q', 'status', 'lang']))
        <a href="{{ route($indexRoute) }}" class="admin-btn admin-btn-secondary">Clear</a>
    @endif
</form>

<div class="admin-card admin-table-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Slug</th>
                <th>Language</th>
                <th>Status</th>
                <th>Modified</th>
                <th class="admin-table-actions-col">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($posts as $post)
            <tr>
                <td><span class="admin-table-title">{{ $post->post_title ?: '(no title)' }}</span></td>
                <td><code class="admin-code">{{ $post->post_name }}</code></td>
                <td><span class="admin-lang-pill">{{ \App\Services\PolylangService::getPostLanguage($post->ID) }}</span></td>
                <td><span class="admin-badge admin-badge-{{ $post->post_status }}">{{ $post->post_status }}</span></td>
                <td class="admin-muted">{{ $post->post_modified?->format('M j, Y') }}</td>
                <td>
                    <div class="admin-table-actions">
                        <a href="{{ route($editRoute, $post->ID) }}" class="admin-btn admin-btn-primary admin-btn-sm">Edit</a>
                        @if($post->post_status === 'publish')
                            <a href="{{ \App\Services\PolylangService::buildPostUrl($post->ID) }}" target="_blank" rel="noopener" class="admin-btn admin-btn-secondary admin-btn-sm">View</a>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="admin-empty">No items found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{ $posts->links() }}
@endsection