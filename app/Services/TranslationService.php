<?php

namespace App\Services;

use App\Models\Language;
use App\Models\PostTranslation;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    private ?array $languages = null;

    public function getLanguages(): array
    {
        if ($this->languages === null) {
            $this->languages = Language::all()->toArray();
        }
        return $this->languages;
    }

    public function getActiveLanguages(): array
    {
        return array_filter($this->getLanguages(), fn($l) => ($l['active'] ?? true));
    }

    public function getPostTranslation(int $postId, string $locale): ?Post
    {
        $translation = PostTranslation::where('post_id', $postId)
            ->whereHas('language', fn($q) => $q->where('locale', $locale))
            ->first();

        if (!$translation) return null;

        return Post::find($translation->post_id);
    }

    public function getTranslationGroup(int $postId): array
    {
        $trans = PostTranslation::where('post_id', $postId)->first();
        if (!$trans) return [];

        return PostTranslation::where('translation_group', $trans->translation_group)
            ->with(['language', 'post'])
            ->get()
            ->keyBy('language_id')
            ->toArray();
    }
}