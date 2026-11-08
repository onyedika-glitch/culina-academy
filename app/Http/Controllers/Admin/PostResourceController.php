<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\PolylangService;
use App\Services\PostAdminService;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class PostResourceController extends Controller
{
    abstract protected function postType(): string;

    abstract protected function resourceLabel(): string;

    abstract protected function indexRoute(): string;

    abstract protected function editRoute(): string;

    protected function showTemplatePicker(): bool
    {
        return $this->postType() === 'page';
    }

    public function index(Request $request)
    {
        $query = Post::query()
            ->where('post_type', $this->postType())
            ->orderByDesc('post_modified');

        if ($search = trim((string) $request->get('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('post_title', 'ilike', "%{$search}%")
                    ->orWhere('post_name', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('post_status', $status);
        }

        if ($lang = $request->get('lang')) {
            $ids = Post::where('post_type', $this->postType())->pluck('ID');
            $filtered = $ids->filter(fn ($id) => PolylangService::getPostLanguage($id) === $lang);
            $query->whereIn('ID', $filtered->all());
        }

        $posts = $query->paginate(25)->withQueryString();

        return view('admin.posts.index', [
            'posts' => $posts,
            'label' => $this->resourceLabel(),
            'postType' => $this->postType(),
            'indexRoute' => $this->indexRoute(),
            'editRoute' => $this->editRoute(),
            'languages' => PolylangService::getLanguages(),
            'statuses' => PostAdminService::statuses(),
        ]);
    }

    public function create()
    {
        return view('admin.posts.edit', $this->editViewData(new Post([
            'post_type' => $this->postType(),
            'post_status' => 'draft',
            'post_content' => '',
            'post_title' => '',
            'post_name' => '',
            'menu_order' => 0,
        ]), true));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $post = PostAdminService::create($data);
        if (! empty($data['seo'])) {
            SeoService::saveForPost($post->ID, $data['seo']);
        }

        return redirect()
            ->route($this->editRoute(), $post->ID)
            ->with('success', $this->resourceLabel().' created.');
    }

    public function edit(int $id)
    {
        $post = Post::with('meta')->where('post_type', $this->postType())->findOrFail($id);

        return view('admin.posts.edit', $this->editViewData($post, false));
    }

    public function update(Request $request, int $id)
    {
        $post = Post::with('meta')->where('post_type', $this->postType())->findOrFail($id);
        $data = $this->validated($request);
        PostAdminService::update($post, $data);
        if (! empty($data['seo'])) {
            SeoService::saveForPost($post->ID, $data['seo']);
        }

        return redirect()
            ->route($this->editRoute(), $post->ID)
            ->with('success', $this->resourceLabel().' saved.');
    }

    public function destroy(int $id)
    {
        $post = Post::where('post_type', $this->postType())->findOrFail($id);
        $post->update(['post_status' => 'trash']);

        return redirect()
            ->route($this->indexRoute())
            ->with('success', $this->resourceLabel().' moved to trash.');
    }

    protected function editViewData(Post $post, bool $isNew): array
    {
        $acf = $post->ID ? PostAdminService::getEditableAcfFields($post->ID) : ['scalars' => [], 'repeaters' => []];
        $language = $post->ID ? PolylangService::getPostLanguage($post->ID) : 'en';
        $translations = $post->ID ? PolylangService::getTranslationGroup($post->ID) : [];

        return [
            'post' => $post,
            'isNew' => $isNew,
            'label' => $this->resourceLabel(),
            'postType' => $this->postType(),
            'indexRoute' => $this->indexRoute(),
            'editRoute' => $this->editRoute(),
            'templates' => PostAdminService::pageTemplates(),
            'statuses' => PostAdminService::statuses(),
            'languages' => PolylangService::getLanguages(),
            'language' => $language,
            'translations' => $translations,
            'acfScalars' => $acf['scalars'],
            'acfRepeaters' => $acf['repeaters'],
            'showTemplatePicker' => $this->showTemplatePicker(),
            'pageList' => Post::where('post_type', 'page')->where('post_status', 'publish')->orderBy('post_title')->get(['ID', 'post_title']),
            'seo' => $post->ID ? SeoService::getEditableMeta($post->ID) : [],
        ];
    }

    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'post_title' => ['required', 'string', 'max:500'],
            'post_name' => ['nullable', 'string', 'max:200'],
            'post_status' => ['required', 'in:'.implode(',', PostAdminService::statuses())],
            'post_content' => ['nullable', 'string'],
            'post_excerpt' => ['nullable', 'string'],
            'menu_order' => ['nullable', 'integer'],
            'page_template' => ['nullable', 'string'],
            'language' => ['nullable', 'string'],
            'acf_scalars' => ['nullable', 'array'],
            'acf_repeaters' => ['nullable', 'array'],
            'translations' => ['nullable', 'array'],
            'seo' => ['nullable', 'array'],
            'seo.title' => ['nullable', 'string', 'max:255'],
            'seo.description' => ['nullable', 'string', 'max:500'],
            'seo.focus_keyword' => ['nullable', 'string', 'max:120'],
            'seo.canonical_url' => ['nullable', 'string', 'max:500'],
            'seo.robots' => ['nullable', 'string', 'max:120'],
            'seo.facebook_title' => ['nullable', 'string', 'max:255'],
            'seo.facebook_description' => ['nullable', 'string', 'max:500'],
            'seo.facebook_image' => ['nullable', 'string', 'max:500'],
            'seo.facebook_image_id' => ['nullable'],
            'seo.twitter_title' => ['nullable', 'string', 'max:255'],
            'seo.twitter_description' => ['nullable', 'string', 'max:500'],
            'seo.twitter_image' => ['nullable', 'string', 'max:500'],
            'seo.twitter_image_id' => ['nullable'],
        ]);

        $validated['post_type'] = $this->postType();
        $validated['post_name'] = $validated['post_name'] ?: Str::slug($validated['post_title']);

        return $validated;
    }
}
