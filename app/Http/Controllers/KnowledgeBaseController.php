<?php

namespace App\Http\Controllers;

use App\Http\Requests\KnowledgeBase\StoreArticleRequest;
use App\Http\Requests\KnowledgeBase\UpdateArticleRequest;
use App\Models\Category;
use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseCategory;
use App\Models\Tag;
use App\Services\KnowledgeBaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KnowledgeBaseController extends Controller
{
    public function __construct(private readonly KnowledgeBaseService $kb)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', KnowledgeBaseArticle::class);

        $articles = $this->kb->search(
            $request->string('search')->toString(),
            $request->integer('knowledge_base_category_id') ?: null,
        );

        return view('knowledge-base.index', [
            'articles' => $articles,
            'kbCategories' => KnowledgeBaseCategory::withCount('articles')->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, KnowledgeBaseArticle $article): View
    {
        $this->authorize('view', $article);

        $article->load(['kbCategory', 'ticketCategory', 'author', 'tags']);

        if ($article->is_published) {
            $article->incrementViewCount();
        }

        $related = KnowledgeBaseArticle::published()
            ->where('id', '!=', $article->id)
            ->where('knowledge_base_category_id', $article->knowledge_base_category_id)
            ->latest()
            ->limit(4)
            ->get();

        return view('knowledge-base.show', compact('article', 'related'));
    }

    public function create(): View
    {
        $this->authorize('create', KnowledgeBaseArticle::class);

        return view('knowledge-base.create', [
            'kbCategories' => KnowledgeBaseCategory::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function store(StoreArticleRequest $request): RedirectResponse
    {
        $article = $this->kb->create($request->validated(), $request->user(), $request);

        return redirect()->route('knowledge-base.show', $article)->with('status', 'article-created');
    }

    public function edit(KnowledgeBaseArticle $article): View
    {
        $this->authorize('update', $article);

        return view('knowledge-base.edit', [
            'article' => $article->load('tags'),
            'kbCategories' => KnowledgeBaseCategory::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateArticleRequest $request, KnowledgeBaseArticle $article): RedirectResponse
    {
        $this->kb->update($article, $request->validated(), $request->user(), $request);

        return redirect()->route('knowledge-base.show', $article)->with('status', 'article-updated');
    }

    public function destroy(Request $request, KnowledgeBaseArticle $article): RedirectResponse
    {
        $this->authorize('delete', $article);
        $this->kb->delete($article, $request->user(), $request);

        return redirect()->route('knowledge-base.index')->with('status', 'article-deleted');
    }

    public function trashed(): View
    {
        $this->authorize('viewAny', KnowledgeBaseArticle::class);

        $articles = KnowledgeBaseArticle::onlyTrashed()
            ->with(['kbCategory', 'author'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('knowledge-base.trashed', compact('articles'));
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $article = KnowledgeBaseArticle::withTrashed()->findOrFail($id);
        $this->authorize('restore', $article);
        $this->kb->restore($article, $request->user(), $request);

        return redirect()->route('knowledge-base.show', $article)->with('status', 'article-restored');
    }
}