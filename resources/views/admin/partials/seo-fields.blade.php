@php $seo = $seo ?? []; @endphp
<div class="admin-card">
    <h3>SEO (Rank Math)</h3>
    <div class="admin-field">
        <label for="seo_title">SEO title</label>
        <input type="text" id="seo_title" name="seo[title]" value="{{ old('seo.title', $seo['title'] ?? '') }}" maxlength="70">
        <small class="admin-muted">Shown in browser tab and search results</small>
    </div>
    <div class="admin-field">
        <label for="seo_description">Meta description</label>
        <textarea id="seo_description" name="seo[description]" rows="3" maxlength="320">{{ old('seo.description', $seo['description'] ?? '') }}</textarea>
    </div>
    <div class="admin-field">
        <label for="seo_focus_keyword">Focus keyword</label>
        <input type="text" id="seo_focus_keyword" name="seo[focus_keyword]" value="{{ old('seo.focus_keyword', $seo['focus_keyword'] ?? '') }}">
    </div>
    <div class="admin-field">
        <label for="seo_canonical">Canonical URL</label>
        <input type="url" id="seo_canonical" name="seo[canonical_url]" value="{{ old('seo.canonical_url', $seo['canonical_url'] ?? '') }}">
    </div>
    <div class="admin-field">
        <label for="seo_robots">Robots</label>
        <select id="seo_robots" name="seo[robots]">
            @foreach(['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow'] as $robots)
                <option value="{{ $robots }}" @selected(old('seo.robots', $seo['robots'] ?? 'index, follow') === $robots)>{{ $robots }}</option>
            @endforeach
        </select>
    </div>

    <details class="admin-seo-advanced">
        <summary style="cursor:pointer;font-weight:600;margin:1rem 0 0.5rem">Social / Open Graph</summary>
        <div class="admin-field">
            <label>Facebook title</label>
            <input type="text" name="seo[facebook_title]" value="{{ old('seo.facebook_title', $seo['facebook_title'] ?? '') }}">
        </div>
        <div class="admin-field">
            <label>Facebook description</label>
            <textarea name="seo[facebook_description]" rows="2">{{ old('seo.facebook_description', $seo['facebook_description'] ?? '') }}</textarea>
        </div>
        <div class="admin-field admin-image-field" data-field-type="image">
            <label>Facebook image</label>
            <div class="admin-image-control">
                <input type="hidden" name="seo[facebook_image_id]" value="{{ old('seo.facebook_image_id', $seo['facebook_image_id'] ?? '') }}" class="admin-image-id">
                <input type="text" name="seo[facebook_image]" value="{{ old('seo.facebook_image', $seo['facebook_image'] ?? '') }}" class="admin-image-url" placeholder="Image URL or pick from library">
                <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm admin-pick-image">Choose</button>
            </div>
            @php $fbImg = $seo['facebook_image'] ?? ''; @endphp
            @if($fbImg)<img src="{{ $fbImg }}" class="admin-image-preview" alt="">@else<img src="" class="admin-image-preview" alt="" style="display:none">@endif
        </div>
        <div class="admin-field">
            <label>Twitter title</label>
            <input type="text" name="seo[twitter_title]" value="{{ old('seo.twitter_title', $seo['twitter_title'] ?? '') }}">
        </div>
        <div class="admin-field">
            <label>Twitter description</label>
            <textarea name="seo[twitter_description]" rows="2">{{ old('seo.twitter_description', $seo['twitter_description'] ?? '') }}</textarea>
        </div>
        <div class="admin-field admin-image-field" data-field-type="image">
            <label>Twitter image</label>
            <div class="admin-image-control">
                <input type="hidden" name="seo[twitter_image_id]" value="{{ old('seo.twitter_image_id', $seo['twitter_image_id'] ?? '') }}" class="admin-image-id">
                <input type="text" name="seo[twitter_image]" value="{{ old('seo.twitter_image', $seo['twitter_image'] ?? '') }}" class="admin-image-url" placeholder="Image URL or pick from library">
                <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm admin-pick-image">Choose</button>
            </div>
            @php $twImg = $seo['twitter_image'] ?? ''; @endphp
            @if($twImg)<img src="{{ $twImg }}" class="admin-image-preview" alt="">@else<img src="" class="admin-image-preview" alt="" style="display:none">@endif
        </div>
    </details>
</div>

@include('admin.partials.media-modal')
@once
@push('scripts')
<script src="/js/admin-media.js"></script>
@endpush
@endonce