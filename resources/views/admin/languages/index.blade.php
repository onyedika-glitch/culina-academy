@extends('admin.layout')

@section('title', 'Languages')
@section('heading', 'Languages & Translations')

@section('content')
<div class="admin-card" style="padding:0;overflow:hidden">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Language</th>
                <th>Code</th>
                <th>Locale</th>
                <th>Pages</th>
                <th>Courses</th>
                <th>Blog</th>
            </tr>
        </thead>
        <tbody>
        @foreach($languages as $lang)
            <tr>
                <td><strong>{{ $lang['name'] }}</strong></td>
                <td><code>{{ $lang['slug'] }}</code></td>
                <td class="admin-muted">{{ $lang['locale'] }}</td>
                <td>{{ $counts[$lang['slug']]['pages'] ?? 0 }}</td>
                <td>{{ $counts[$lang['slug']]['courses'] ?? 0 }}</td>
                <td>{{ $counts[$lang['slug']]['posts'] ?? 0 }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="admin-card">
    <h3>Managing translations</h3>
    <p>Polylang data is stored in WordPress taxonomies. When editing a page, course, or blog post, use the <strong>Translations</strong> panel to link post IDs across languages.</p>
    <p class="admin-muted">Public URLs always use language prefixes: /en/, /fr/, /es/, /it/, /zh/, /zh-tw/. Visiting / redirects to /en.</p>
    <div class="admin-actions" style="margin-top:1rem">
        <a href="{{ route('admin.pages.index') }}" class="admin-btn admin-btn-secondary admin-btn-sm">Edit pages</a>
        <a href="{{ route('admin.menus.index') }}" class="admin-btn admin-btn-secondary admin-btn-sm">Edit menus</a>
    </div>
</div>
@endsection