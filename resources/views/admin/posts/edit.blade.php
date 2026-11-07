@extends('admin.layout')

@section('title', ($isNew ? 'New ' : 'Edit ') . $label)
@section('heading', ($isNew ? 'New ' : 'Edit ') . $label)

@section('content')
<form method="POST" action="{{ $isNew ? route(str_replace('.edit', '.store', $editRoute)) : route(str_replace('.edit', '.update', $editRoute), $post->ID) }}">
    @csrf
    @if(!$isNew) @method('PUT') @endif

    <div class="admin-form-grid">
        <div>
            <div class="admin-card">
                <div class="admin-field">
                    <label for="post_title">Title *</label>
                    <input type="text" id="post_title" name="post_title" value="{{ old('post_title', $post->post_title) }}" required>
                </div>
                <div class="admin-field">
                    <label for="post_name">Slug</label>
                    <input type="text" id="post_name" name="post_name" value="{{ old('post_name', $post->post_name) }}">
                </div>
                <div class="admin-field">
                    <label for="post_content">Content</label>
                    <textarea id="post_content" name="post_content" rows="12">{{ old('post_content', $post->post_content) }}</textarea>
                </div>
                <div class="admin-field">
                    <label for="post_excerpt">Excerpt</label>
                    <textarea id="post_excerpt" name="post_excerpt" rows="3">{{ old('post_excerpt', $post->post_excerpt) }}</textarea>
                </div>
            </div>

            @include('admin.partials.acf-fields', [
                'acfScalars' => $acfScalars,
                'acfRepeaters' => $acfRepeaters,
            ])

            @if(!$isNew)
                @include('admin.partials.seo-fields', ['seo' => $seo ?? []])
            @endif
        </div>

        <div>
            <div class="admin-card">
                <h3>Publish</h3>
                <div class="admin-field">
                    <label for="post_status">Status</label>
                    <select id="post_status" name="post_status">
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(old('post_status', $post->post_status) === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="admin-field">
                    <label for="menu_order">Menu order</label>
                    <input type="number" id="menu_order" name="menu_order" value="{{ old('menu_order', $post->menu_order) }}">
                </div>
                <button type="submit" class="admin-btn admin-btn-primary" style="width:100%">Save</button>
                @if(!$isNew)
                    <a href="{{ \App\Services\PolylangService::buildPostUrl($post->ID) }}" target="_blank" class="admin-btn admin-btn-secondary admin-btn-sm" style="width:100%;margin-top:0.5rem;text-align:center">View on site</a>
                @endif
            </div>

            @if($showTemplatePicker)
            <div class="admin-card">
                <h3>Page Template</h3>
                <div class="admin-field">
                    <select name="page_template">
                        @foreach($templates as $value => $label)
                            <option value="{{ $value }}" @selected(old('page_template', $post->getMeta('_wp_page_template', '')) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            <div class="admin-card">
                <h3>Language</h3>
                <div class="admin-field">
                    <select name="language">
                        @foreach($languages as $lang)
                            <option value="{{ $lang['slug'] }}" @selected(old('language', $language) === $lang['slug'])>{{ $lang['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(!$isNew && count($languages) > 1)
            <div class="admin-card">
                <h3>Translations</h3>
                <p class="admin-muted" style="margin-bottom:0.75rem">Link this content to translated posts by ID.</p>
                @foreach($languages as $lang)
                    @if($lang['slug'] === $language) @continue @endif
                    <div class="admin-field">
                        <label>{{ $lang['name'] }} (post ID)</label>
                        <input type="number" name="translations[{{ $lang['slug'] }}]" value="{{ old('translations.'.$lang['slug'], $translations[$lang['slug']] ?? '') }}" placeholder="Post ID">
                        @if(!empty($translations[$lang['slug']]))
                            <a href="{{ route($editRoute, $translations[$lang['slug']]) }}" class="admin-btn admin-btn-secondary admin-btn-sm" style="margin-top:0.35rem">Edit translation</a>
                        @endif
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</form>

@if(!$isNew)
<form method="POST" action="{{ route(str_replace('.edit', '.destroy', $editRoute), $post->ID) }}" style="margin-top:1rem" onsubmit="return confirm('Move this item to trash?')">
    @csrf @method('DELETE')
    <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">Move to trash</button>
</form>
@endif
@endsection