<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PolylangAdminService
{
    public static function setPostLanguage(int $postId, string $langCode): void
    {
        $langCode = PolylangService::normalizeCode($langCode);
        $language = PolylangService::getLanguage($langCode);

        if (!$language) {
            return;
        }

        $termTaxonomyId = DB::table('wp_term_taxonomy')
            ->where('term_id', $language['term_id'])
            ->where('taxonomy', 'language')
            ->value('term_taxonomy_id');

        if (!$termTaxonomyId) {
            return;
        }

        DB::table('wp_term_relationships')
            ->where('object_id', $postId)
            ->whereIn('term_taxonomy_id', static::languageTermTaxonomyIds())
            ->delete();

        DB::table('wp_term_relationships')->insert([
            'object_id' => $postId,
            'term_taxonomy_id' => $termTaxonomyId,
            'term_order' => 0,
        ]);
    }

    /** @param array<string, int> $translations lang code => post id */
    public static function setTranslationGroup(array $translations): void
    {
        $translations = array_filter($translations, fn ($id) => (int) $id > 0);
        if (empty($translations)) {
            return;
        }

        $normalized = [];
        foreach ($translations as $lang => $id) {
            $normalized[PolylangService::normalizeCode($lang)] = (int) $id;
        }

        $postIds = array_values($normalized);
        $existingTermId = static::findSharedTranslationTermId($postIds);

        if ($existingTermId) {
            DB::table('wp_term_taxonomy')
                ->where('term_taxonomy_id', $existingTermId)
                ->update(['description' => serialize($normalized)]);
        } else {
            $termId = DB::table('wp_terms')->insertGetId([
                'name' => 'pll_' . min($postIds),
                'slug' => 'pll_' . min($postIds),
                'term_group' => 0,
            ]);

            $termTaxonomyId = DB::table('wp_term_taxonomy')->insertGetId([
                'term_id' => $termId,
                'taxonomy' => 'post_translations',
                'description' => serialize($normalized),
                'parent' => 0,
                'count' => count($normalized),
            ]);

            foreach ($postIds as $postId) {
                DB::table('wp_term_relationships')
                    ->where('object_id', $postId)
                    ->whereIn('term_taxonomy_id', static::translationTermTaxonomyIds())
                    ->delete();

                DB::table('wp_term_relationships')->insert([
                    'object_id' => $postId,
                    'term_taxonomy_id' => $termTaxonomyId,
                    'term_order' => 0,
                ]);
            }
        }
    }

    public static function linkTranslation(int $postId, string $langCode, int $translationId): void
    {
        $langCode = PolylangService::normalizeCode($langCode);
        $group = PolylangService::getTranslationGroup($postId);
        $group[PolylangService::getPostLanguage($postId)] = $postId;
        $group[$langCode] = $translationId;
        static::setTranslationGroup($group);
    }

    /** @return array<int, int> */
    protected static function languageTermTaxonomyIds(): array
    {
        return DB::table('wp_term_taxonomy')
            ->where('taxonomy', 'language')
            ->pluck('term_taxonomy_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** @return array<int, int> */
    protected static function translationTermTaxonomyIds(): array
    {
        return DB::table('wp_term_taxonomy')
            ->where('taxonomy', 'post_translations')
            ->pluck('term_taxonomy_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** @param array<int, int> $postIds */
    protected static function findSharedTranslationTermId(array $postIds): ?int
    {
        foreach ($postIds as $postId) {
            $termId = DB::table('wp_term_relationships')
                ->join('wp_term_taxonomy', 'wp_term_relationships.term_taxonomy_id', '=', 'wp_term_taxonomy.term_taxonomy_id')
                ->where('wp_term_relationships.object_id', $postId)
                ->where('wp_term_taxonomy.taxonomy', 'post_translations')
                ->value('wp_term_taxonomy.term_taxonomy_id');

            if ($termId) {
                return (int) $termId;
            }
        }

        return null;
    }
}