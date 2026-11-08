<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\MenuAdminService;
use App\Services\SiteCacheService;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        return view('admin.menus.index', [
            'menus' => MenuAdminService::listMenus(),
        ]);
    }

    public function edit(int $termTaxonomyId)
    {
        $menu = collect(MenuAdminService::listMenus())->firstWhere('term_taxonomy_id', $termTaxonomyId);
        if (! $menu) {
            abort(404);
        }

        return view('admin.menus.edit', [
            'menu' => $menu,
            'items' => MenuAdminService::getMenuItems($termTaxonomyId),
            'pages' => Post::where('post_type', 'page')->where('post_status', 'publish')->orderBy('post_title')->get(['ID', 'post_title']),
        ]);
    }

    public function update(Request $request, int $termTaxonomyId)
    {
        $data = $request->validate([
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.title' => ['nullable', 'string'],
            'items.*.url' => ['nullable', 'string'],
            'items.*.object_id' => ['nullable', 'integer'],
            'items.*.parent' => ['nullable', 'integer'],
            'items.*.target' => ['nullable', 'string'],
        ]);

        MenuAdminService::saveMenuItems($termTaxonomyId, $data['items'] ?? []);
        SiteCacheService::clearMenus();

        return redirect()
            ->route('admin.menus.edit', $termTaxonomyId)
            ->with('success', 'Menu saved.');
    }
}
