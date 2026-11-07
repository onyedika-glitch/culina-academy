<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PolylangService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    private function getCategorySlug(): string
    {
        $lang = PolylangService::currentLanguage();
        $categoryMap = [
            'en' => 'blog',
            'fr' => 'blogs-fr',
            'it' => 'blogs-it',
            'es' => 'blogs-es',
        ];
        return $categoryMap[$lang] ?? 'blog';
    }

    public function index()
    {
        $page = cal_page_by_slug('blog');

        if ($page) {
            cal_bind_post($page);
        }

        $categorySlug = $this->getCategorySlug();

        $posts = Post::with('meta')
            ->where('post_type', 'post')
            ->published()
            ->whereExists(function ($query) use ($categorySlug) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('wp_term_relationships')
                    ->join('wp_term_taxonomy', 'wp_term_relationships.term_taxonomy_id', '=', 'wp_term_taxonomy.term_taxonomy_id')
                    ->join('wp_terms', 'wp_term_taxonomy.term_id', '=', 'wp_terms.term_id')
                    ->whereColumn('wp_term_relationships.object_id', 'wp_posts.ID')
                    ->where('wp_term_taxonomy.taxonomy', 'category')
                    ->where('wp_terms.slug', $categorySlug);
            })
            ->orderBy('post_date', 'desc')
            ->paginate(8);

        $bodyClass = cal_body_class_for_view('site.blog');

        return view('site.blog', compact('posts', 'page', 'bodyClass'));
    }

    public function loadMore(Request $request)
    {
        $page = max(1, (int) $request->input('page', 2));
        $categorySlug = $this->getCategorySlug();

        $posts = Post::with('meta')
            ->where('post_type', 'post')
            ->published()
            ->whereExists(function ($query) use ($categorySlug) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('wp_term_relationships')
                    ->join('wp_term_taxonomy', 'wp_term_relationships.term_taxonomy_id', '=', 'wp_term_taxonomy.term_taxonomy_id')
                    ->join('wp_terms', 'wp_term_taxonomy.term_id', '=', 'wp_terms.term_id')
                    ->whereColumn('wp_term_relationships.object_id', 'wp_posts.ID')
                    ->where('wp_term_taxonomy.taxonomy', 'category')
                    ->where('wp_terms.slug', $categorySlug);
            })
            ->orderBy('post_date', 'desc')
            ->paginate(8, ['*'], 'page', $page);

        if ($posts->isEmpty()) {
            return response()->json([
                'items' => '',
                'status' => 'no-more-posts',
            ]);
        }

        $html = '';
        foreach ($posts as $post) {
            $html .= view('partials.blog-card', compact('post'))->render();
        }

        return response()->json([
            'items' => $html,
            'status' => $posts->hasMorePages() ? 'have-posts' : 'no-more-posts',
        ]);
    }

    public function show(Request $request)
    {
        $slug = $request->route('slug');
        $categorySlug = $this->getCategorySlug();

        $post = Post::with('meta')
            ->where('post_name', $slug)
            ->where('post_type', 'post')
            ->published()
            ->whereExists(function ($query) use ($categorySlug) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('wp_term_relationships')
                    ->join('wp_term_taxonomy', 'wp_term_relationships.term_taxonomy_id', '=', 'wp_term_taxonomy.term_taxonomy_id')
                    ->join('wp_terms', 'wp_term_taxonomy.term_id', '=', 'wp_terms.term_id')
                    ->whereColumn('wp_term_relationships.object_id', 'wp_posts.ID')
                    ->where('wp_term_taxonomy.taxonomy', 'category')
                    ->where('wp_terms.slug', $categorySlug);
            })
            ->first();

        // Fallback: find by slug without category filter (for uncategorised legacy posts)
        if (!$post) {
            $post = Post::with('meta')
                ->where('post_name', $slug)
                ->where('post_type', 'post')
                ->published()
                ->first();
        }

        if (!$post) {
            abort(404);
        }

        cal_bind_post($post);

        $fields = [];
        foreach ($post->meta as $meta) {
            if (!str_starts_with($meta->meta_key, '_')) {
                $fields[$meta->meta_key] = $meta->meta_value;
            }
        }

        $recent = Post::where('post_type', 'post')
            ->published()
            ->where('ID', '!=', $post->ID)
            ->whereExists(function ($query) use ($categorySlug) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('wp_term_relationships')
                    ->join('wp_term_taxonomy', 'wp_term_relationships.term_taxonomy_id', '=', 'wp_term_taxonomy.term_taxonomy_id')
                    ->join('wp_terms', 'wp_term_taxonomy.term_id', '=', 'wp_terms.term_id')
                    ->whereColumn('wp_term_relationships.object_id', 'wp_posts.ID')
                    ->where('wp_term_taxonomy.taxonomy', 'category')
                    ->where('wp_terms.slug', $categorySlug);
            })
            ->orderBy('post_date', 'desc')
            ->limit(3)
            ->get();

        $page = $post;
        $bodyClass = 'no-sidebar full-width-content single-post';

        return view('site.blog-single', compact('post', 'fields', 'recent', 'page', 'bodyClass'));
    }
}