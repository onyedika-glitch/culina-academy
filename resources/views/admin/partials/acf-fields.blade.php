@php
    use App\Services\AcfFieldTypeService;
    $scalarPrefix = $scalarPrefix ?? 'acf_scalars';
    $repeaterPrefix = $repeaterPrefix ?? 'acf_repeaters';
    $groupedScalars = [];
    foreach ($acfScalars ?? [] as $key => $value) {
        $groupedScalars[AcfFieldTypeService::groupKey($key)][$key] = $value;
    }
@endphp

@if(!empty($groupedScalars))
    @foreach($groupedScalars as $groupName => $fields)
    <div class="admin-card">
        <h3>{{ $groupName }}</h3>
        @foreach($fields as $key => $value)
            @php $type = AcfFieldTypeService::detect($key, $value); @endphp
            <div class="admin-field admin-field-{{ $type }}" data-field-type="{{ $type }}">
                <label for="scalar_{{ $key }}">{{ AcfFieldTypeService::humanLabel($key) }}</label>

                @if($type === 'image')
                    @php $preview = AcfFieldTypeService::imagePreview($value); $stored = AcfFieldTypeService::imageStoredValue($value); @endphp
                    <div class="admin-image-control">
                        <input type="hidden" id="scalar_{{ $key }}" name="{{ $scalarPrefix }}[{{ $key }}]" value="{{ old($scalarPrefix.'.'.$key, $stored) }}" class="admin-image-id">
                        <input type="text" class="admin-image-url" value="{{ $preview }}" placeholder="Attachment ID or URL" readonly>
                        <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm admin-pick-image">Choose image</button>
                        <button type="button" class="admin-btn admin-btn-danger admin-btn-sm admin-clear-image">Clear</button>
                    </div>
                    <img src="{{ $preview }}" class="admin-image-preview" alt="" @if(!$preview) style="display:none" @endif>
                    <small class="admin-muted">{{ $key }}</small>

                @elseif($type === 'link')
                    @php
                        $link = is_array($value) ? $value : ['url' => '', 'title' => '', 'target' => ''];
                    @endphp
                    <div class="admin-link-field">
                        <input type="text" name="{{ $scalarPrefix }}[{{ $key }}][title]" value="{{ $link['title'] ?? '' }}" placeholder="Link text">
                        <input type="url" name="{{ $scalarPrefix }}[{{ $key }}][url]" value="{{ $link['url'] ?? '' }}" placeholder="https://">
                        <select name="{{ $scalarPrefix }}[{{ $key }}][target]">
                            <option value="" @selected(empty($link['target']))>Same window</option>
                            <option value="_blank" @selected(($link['target'] ?? '') === '_blank')>New tab</option>
                        </select>
                    </div>
                    <small class="admin-muted">{{ $key }}</small>

                @elseif($type === 'boolean')
                    <select id="scalar_{{ $key }}" name="{{ $scalarPrefix }}[{{ $key }}]">
                        <option value="1" @selected($value)>Yes</option>
                        <option value="0" @selected(!$value)>No</option>
                    </select>

                @elseif($type === 'textarea')
                    <textarea id="scalar_{{ $key }}" name="{{ $scalarPrefix }}[{{ $key }}]" rows="5">{{ is_scalar($value) ? $value : '' }}</textarea>

                @elseif($type === 'url')
                    <input type="url" id="scalar_{{ $key }}" name="{{ $scalarPrefix }}[{{ $key }}]" value="{{ is_scalar($value) ? $value : '' }}">

                @else
                    <input type="text" id="scalar_{{ $key }}" name="{{ $scalarPrefix }}[{{ $key }}]" value="{{ is_scalar($value) ? $value : '' }}">
                @endif
            </div>
        @endforeach
    </div>
    @endforeach
@endif

@if(!empty($acfRepeaters))
<div class="admin-card">
    <h3>Repeater Fields</h3>
    @foreach($acfRepeaters as $fieldName => $rows)
        <div class="admin-repeater" data-repeater="{{ $fieldName }}">
            <div class="admin-repeater-header">
                <span>{{ AcfFieldTypeService::humanLabel($fieldName) }} ({{ count($rows) }} rows)</span>
                <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" onclick="addRepeaterRow('{{ $fieldName }}')">+ Add row</button>
            </div>
            <div class="admin-repeater-rows" id="repeater-{{ $fieldName }}">
                @foreach($rows as $rowIndex => $row)
                    @if(!is_array($row)) @continue @endif
                    <div class="admin-repeater-row" data-row="{{ $rowIndex }}">
                        <div style="display:flex;justify-content:space-between;margin-bottom:0.5rem">
                            <strong>Row {{ $rowIndex + 1 }}</strong>
                            <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" onclick="this.closest('.admin-repeater-row').remove()">Remove</button>
                        </div>
                        @foreach($row as $subKey => $subValue)
                            @php $subType = AcfFieldTypeService::detect($subKey, $subValue); @endphp
                            <div class="admin-field admin-field-{{ $subType }}" data-field-type="{{ $subType }}">
                                <label>{{ AcfFieldTypeService::humanLabel($subKey) }}</label>
                                @if($subType === 'image')
                                    @php $preview = AcfFieldTypeService::imagePreview($subValue); $stored = AcfFieldTypeService::imageStoredValue($subValue); @endphp
                                    <div class="admin-image-control">
                                        <input type="hidden" name="{{ $repeaterPrefix }}[{{ $fieldName }}][{{ $rowIndex }}][{{ $subKey }}]" value="{{ $stored }}" class="admin-image-id">
                                        <input type="text" class="admin-image-url" value="{{ $preview }}" readonly>
                                        <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm admin-pick-image">Choose</button>
                                    </div>
                                    <img src="{{ $preview }}" class="admin-image-preview" alt="" @if(!$preview) style="display:none" @endif>
                                @elseif($subType === 'link' && is_array($subValue))
                                    <div class="admin-link-field">
                                        <input type="text" name="{{ $repeaterPrefix }}[{{ $fieldName }}][{{ $rowIndex }}][{{ $subKey }}][title]" value="{{ $subValue['title'] ?? '' }}" placeholder="Text">
                                        <input type="url" name="{{ $repeaterPrefix }}[{{ $fieldName }}][{{ $rowIndex }}][{{ $subKey }}][url]" value="{{ $subValue['url'] ?? '' }}" placeholder="URL">
                                    </div>
                                @elseif($subType === 'textarea')
                                    <textarea name="{{ $repeaterPrefix }}[{{ $fieldName }}][{{ $rowIndex }}][{{ $subKey }}]" rows="3">{{ is_scalar($subValue) ? $subValue : '' }}</textarea>
                                @else
                                    <input type="text" name="{{ $repeaterPrefix }}[{{ $fieldName }}][{{ $rowIndex }}][{{ $subKey }}]" value="{{ is_scalar($subValue) ? $subValue : '' }}">
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endif

@include('admin.partials.media-modal')

@once
@push('scripts')
<script src="/js/admin-media.js"></script>
<script>
function addRepeaterRow(fieldName) {
    const container = document.getElementById('repeater-' + fieldName);
    if (!container) return;
    const existing = container.querySelector('.admin-repeater-row');
    const rowIndex = container.querySelectorAll('.admin-repeater-row').length;
    const row = document.createElement('div');
    row.className = 'admin-repeater-row';
    row.dataset.row = rowIndex;

    if (existing) {
        row.innerHTML = existing.innerHTML.replace(/\[(\d+)\]/g, '[' + rowIndex + ']');
        row.querySelectorAll('input, textarea').forEach(el => {
            if (el.type !== 'hidden') el.value = '';
            if (el.classList.contains('admin-image-id')) el.value = '';
        });
        row.querySelectorAll('.admin-image-preview').forEach(img => { img.src = ''; img.style.display = 'none'; });
    } else {
        row.innerHTML = '<div class="admin-field"><label>value</label><input type="text" name="acf_repeaters[' + fieldName + '][' + rowIndex + '][value]"></div>';
    }

    const header = document.createElement('div');
    header.style.cssText = 'display:flex;justify-content:space-between;margin-bottom:0.5rem';
    header.innerHTML = '<strong>Row ' + (rowIndex + 1) + '</strong><button type="button" class="admin-btn admin-btn-danger admin-btn-sm" onclick="this.closest(\'.admin-repeater-row\').remove()">Remove</button>';
    row.prepend(header);
    container.appendChild(row);
}
</script>
@endpush
@endonce