@extends('admin.layout')

@section('title', 'Menus')
@section('heading', 'Navigation Menus')

@section('content')
<div class="admin-card" style="padding:0;overflow:hidden">
    <table class="admin-table">
        <thead>
            <tr><th>Menu</th><th>Slug</th><th>Items</th><th></th></tr>
        </thead>
        <tbody>
        @foreach($menus as $menu)
            <tr>
                <td><strong>{{ $menu['name'] }}</strong></td>
                <td class="admin-muted"><code>{{ $menu['slug'] }}</code></td>
                <td>{{ $menu['count'] }}</td>
                <td><a href="{{ route('admin.menus.edit', $menu['term_taxonomy_id']) }}" class="admin-btn admin-btn-secondary admin-btn-sm">Edit items</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<p class="admin-muted">Menus map to Polylang language variants (e.g. primary-menu-fr for French). Changes appear on the public site immediately.</p>
@endsection