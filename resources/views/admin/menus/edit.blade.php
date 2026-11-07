@extends('admin.layout')

@section('title', 'Edit Menu')
@section('heading', 'Edit: ' . $menu['name'])

@section('content')
<form method="POST" action="{{ route('admin.menus.update', $menu['term_taxonomy_id']) }}">
    @csrf @method('PUT')

    <div class="admin-card" style="padding:0">
        <div class="admin-menu-item" style="background:var(--admin-bg);font-weight:600;font-size:0.75rem">
            <span>Title</span>
            <span>URL</span>
            <span>Page</span>
            <span>Parent ID</span>
            <span></span>
        </div>
        <div id="menu-items">
            @foreach($items as $index => $item)
                <div class="admin-menu-item">
                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item['id'] }}">
                    <input type="text" name="items[{{ $index }}][title]" value="{{ $item['title'] }}" placeholder="Label">
                    <input type="text" name="items[{{ $index }}][url]" value="{{ $item['url'] }}" placeholder="https:// or /path">
                    <select name="items[{{ $index }}][object_id]">
                        <option value="0">— Custom URL —</option>
                        @foreach($pages as $page)
                            <option value="{{ $page->ID }}" @selected($item['object_id'] == $page->ID)>{{ $page->post_title }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="items[{{ $index }}][parent]" value="{{ $item['parent'] }}" min="0" placeholder="0">
                    <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" onclick="this.closest('.admin-menu-item').remove()">×</button>
                </div>
            @endforeach
        </div>
    </div>

    <div class="admin-actions" style="margin-top:1rem">
        <button type="button" class="admin-btn admin-btn-secondary" onclick="addMenuItem()">+ Add item</button>
        <button type="submit" class="admin-btn admin-btn-primary">Save menu</button>
        <a href="{{ route('admin.menus.index') }}" class="admin-btn admin-btn-secondary">Back</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
let menuIndex = {{ count($items) }};
const pageOptions = `{!! collect($pages)->map(fn($p) => '<option value="'.$p->ID.'">'.e($p->post_title).'</option>')->implode('') !!}`;

function addMenuItem() {
    const row = document.createElement('div');
    row.className = 'admin-menu-item';
    row.innerHTML = `
        <input type="hidden" name="items[${menuIndex}][id]" value="0">
        <input type="text" name="items[${menuIndex}][title]" placeholder="Label">
        <input type="text" name="items[${menuIndex}][url]" placeholder="https:// or /path">
        <select name="items[${menuIndex}][object_id]"><option value="0">— Custom URL —</option>${pageOptions}</select>
        <input type="number" name="items[${menuIndex}][parent]" value="0" min="0">
        <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" onclick="this.closest('.admin-menu-item').remove()">×</button>
    `;
    document.getElementById('menu-items').appendChild(row);
    menuIndex++;
}
</script>
@endpush