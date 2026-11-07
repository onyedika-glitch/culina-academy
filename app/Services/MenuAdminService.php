<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\DB;

class MenuAdminService
{
    /** @return array<int, array{term_taxonomy_id:int,slug:string,name:string,count:int}> */
    public static function listMenus(): array
    {
        return DB::table('wp_terms')
            ->join('wp_term_taxonomy', 'wp_terms.term_id', '=', 'wp_term_taxonomy.term_id')
            ->where('wp_term_taxonomy.taxonomy', 'nav_menu')
            ->select('wp_term_taxonomy.term_taxonomy_id', 'wp_terms.slug', 'wp_terms.name', 'wp_term_taxonomy.count')
            ->orderBy('wp_terms.name')
            ->get()
            ->map(fn ($row) => [
                'term_taxonomy_id' => (int) $row->term_taxonomy_id,
                'slug' => (string) $row->slug,
                'name' => (string) $row->name,
                'count' => (int) $row->count,
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public static function getMenuItems(int $termTaxonomyId): array
    {
        $objectIds = DB::table('wp_term_relationships')
            ->where('term_taxonomy_id', $termTaxonomyId)
            ->pluck('object_id');

        $posts = Post::with('meta')
            ->whereIn('ID', $objectIds)
            ->where('post_type', 'nav_menu_item')
            ->orderBy('menu_order')
            ->get();

        $items = [];
        foreach ($posts as $item) {
            $objectId = (int) $item->getMeta('_menu_item_object_id', 0);
            $title = $item->post_title;
            if ($title === '' && $objectId) {
                $title = Post::find($objectId)?->post_title ?? 'Link';
            }

            $items[] = [
                'id' => $item->ID,
                'title' => $title,
                'url' => (string) $item->getMeta('_menu_item_url', ''),
                'type' => (string) $item->getMeta('_menu_item_type', 'custom'),
                'object' => (string) $item->getMeta('_menu_item_object', 'custom'),
                'object_id' => $objectId,
                'parent' => (int) $item->getMeta('_menu_item_menu_item_parent', 0),
                'target' => (string) $item->getMeta('_menu_item_target', ''),
                'order' => (int) $item->menu_order,
            ];
        }

        return $items;
    }

    /** @param array<int, array<string, mixed>> $items */
    public static function saveMenuItems(int $termTaxonomyId, array $items): void
    {
        $existingIds = DB::table('wp_term_relationships')
            ->where('term_taxonomy_id', $termTaxonomyId)
            ->pluck('object_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $keptIds = [];
        $now = now();

        foreach ($items as $index => $item) {
            $itemId = (int) ($item['id'] ?? 0);
            $title = trim((string) ($item['title'] ?? 'Menu Item'));
            $type = ($item['object_id'] ?? 0) ? 'post_type' : 'custom';
            $object = ($item['object_id'] ?? 0) ? 'page' : 'custom';
            $objectId = (int) ($item['object_id'] ?? 0);
            $url = trim((string) ($item['url'] ?? ''));
            $parent = (int) ($item['parent'] ?? 0);
            $target = trim((string) ($item['target'] ?? ''));

            if ($itemId && in_array($itemId, $existingIds, true)) {
                DB::table('wp_posts')->where('ID', $itemId)->update([
                    'post_title' => $title,
                    'menu_order' => $index,
                    'post_modified' => $now,
                    'post_modified_gmt' => $now->utc(),
                ]);
                static::setMenuItemMeta($itemId, $type, $object, $objectId, $url, $parent, $target);
                $keptIds[] = $itemId;
                continue;
            }

            $newId = DB::table('wp_posts')->insertGetId([
                'post_author' => auth('admin')->id() ?: 1,
                'post_date' => $now,
                'post_date_gmt' => $now->utc(),
                'post_content' => '',
                'post_title' => $title,
                'post_excerpt' => '',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_password' => '',
                'post_name' => 'menu-item-' . uniqid(),
                'to_ping' => '',
                'pinged' => '',
                'post_modified' => $now,
                'post_modified_gmt' => $now->utc(),
                'post_content_filtered' => '',
                'post_parent' => 0,
                'guid' => '',
                'menu_order' => $index,
                'post_type' => 'nav_menu_item',
                'post_mime_type' => '',
                'comment_count' => 0,
            ]);

            static::setMenuItemMeta($newId, $type, $object, $objectId, $url, $parent, $target);

            DB::table('wp_term_relationships')->insert([
                'object_id' => $newId,
                'term_taxonomy_id' => $termTaxonomyId,
                'term_order' => 0,
            ]);

            $keptIds[] = $newId;
        }

        $removeIds = array_diff($existingIds, $keptIds);
        foreach ($removeIds as $removeId) {
            DB::table('wp_term_relationships')
                ->where('term_taxonomy_id', $termTaxonomyId)
                ->where('object_id', $removeId)
                ->delete();
            DB::table('wp_posts')->where('ID', $removeId)->delete();
            DB::table('wp_postmeta')->where('post_id', $removeId)->delete();
        }

        DB::table('wp_term_taxonomy')
            ->where('term_taxonomy_id', $termTaxonomyId)
            ->update(['count' => count($keptIds)]);
    }

    protected static function setMenuItemMeta(
        int $itemId,
        string $type,
        string $object,
        int $objectId,
        string $url,
        int $parent,
        string $target,
    ): void {
        PostMetaWriter::set($itemId, '_menu_item_type', $type);
        PostMetaWriter::set($itemId, '_menu_item_object', $object);
        PostMetaWriter::set($itemId, '_menu_item_object_id', (string) $objectId);
        PostMetaWriter::set($itemId, '_menu_item_url', $url);
        PostMetaWriter::set($itemId, '_menu_item_menu_item_parent', (string) $parent);
        PostMetaWriter::set($itemId, '_menu_item_target', $target);
    }
}