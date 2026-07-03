<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\KnowledgeBaseArticle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Business logic untuk Knowledge Base. Controller tidak boleh menyentuh
 * Eloquent langsung untuk operasi tulis, konsisten dengan pola TicketService.
 */
class KnowledgeBaseService
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function create(array $data, User $author, Request $request): KnowledgeBaseArticle
    {
        return DB::transaction(function () use ($data, $author, $request) {
            $article = KnowledgeBaseArticle::create([
                'title' => $data['title'],
                'slug' => $this->generateSlug($data['title']),
                'content' => $data['content'],
                'excerpt' => $data['excerpt'] ?? Str::limit(strip_tags($data['content']), 150),
                'knowledge_base_category_id' => $data['knowledge_base_category_id'],
                'category_id' => $data['category_id'] ?? null,
                'created_by' => $author->id,
                'is_published' => $data['is_published'] ?? true,
            ]);

            if (! empty($data['tags'])) {
                $article->tags()->sync($data['tags']);
            }

            $this->auditLogger->log(AuditAction::CREATE, $author, $request, $article, null, $article->only([
                'title', 'knowledge_base_category_id', 'is_published',
            ]));

            return $article->fresh();
        });
    }

    public function update(KnowledgeBaseArticle $article, array $data, User $actor, Request $request): KnowledgeBaseArticle
    {
        return DB::transaction(function () use ($article, $data, $actor, $request) {
            $old = $article->only(['title', 'content', 'excerpt', 'knowledge_base_category_id', 'category_id', 'is_published']);

            if ($data['title'] !== $article->title) {
                $article->slug = $this->generateSlug($data['title'], $article->id);
            }

            $article->fill([
                'title' => $data['title'],
                'content' => $data['content'],
                'excerpt' => $data['excerpt'] ?? Str::limit(strip_tags($data['content']), 150),
                'knowledge_base_category_id' => $data['knowledge_base_category_id'],
                'category_id' => $data['category_id'] ?? null,
                'is_published' => $data['is_published'] ?? $article->is_published,
            ]);
            $article->save();

            if (isset($data['tags'])) {
                $article->tags()->sync($data['tags']);
            }

            $this->auditLogger->log(AuditAction::UPDATE, $actor, $request, $article, $old, $article->only([
                'title', 'content', 'excerpt', 'knowledge_base_category_id', 'category_id', 'is_published',
            ]));

            return $article->fresh();
        });
    }

    public function delete(KnowledgeBaseArticle $article, User $actor, Request $request): void
    {
        $article->delete();
        $this->auditLogger->log(AuditAction::DELETE, $actor, $request, $article);
    }

    public function restore(KnowledgeBaseArticle $article, User $actor, Request $request): KnowledgeBaseArticle
    {
        $article->restore();
        $this->auditLogger->log(AuditAction::RESTORE, $actor, $request, $article);

        return $article->fresh();
    }

    /**
     * Full-text search untuk halaman index KB. `whereFullText` butuh MySQL/Postgres;
     * kalau koneksi aktif sqlite (default project ini), fallback ke LIKE supaya
     * tetap berfungsi tanpa migration tambahan (index FULLTEXT sudah ada di skema
     * untuk produksi MySQL — lihat 2024_01_01_000027_create_knowledge_base_articles_table).
     */
    public function search(string $term, ?int $knowledgeBaseCategoryId = null)
    {
        $query = KnowledgeBaseArticle::query()->published()->with(['kbCategory', 'tags']);

        if ($knowledgeBaseCategoryId) {
            $query->where('knowledge_base_category_id', $knowledgeBaseCategoryId);
        }

        if ($term !== '') {
            if (DB::connection()->getDriverName() === 'mysql') {
                $query->whereFullText(['title', 'content'], $term);
            } else {
                $query->where(function ($q) use ($term) {
                    $q->where('title', 'like', "%{$term}%")
                        ->orWhere('content', 'like', "%{$term}%")
                        ->orWhere('excerpt', 'like', "%{$term}%");
                });
            }
        }

        return $query->latest()->paginate(12)->withQueryString();
    }

    /**
     * AI Suggested Solution (bonus feature): cari artikel relevan berdasarkan
     * kata kunci signifikan (>3 huruf) dari subject/description ticket yang
     * sedang diketik user, diprioritaskan dari kategori ticket yang sama.
     */
    public function suggest(string $text, ?int $ticketCategoryId = null, int $limit = 5): Collection
    {
        $words = collect(preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text)))
            ->filter(fn ($w) => mb_strlen($w) > 3)
            ->unique()
            ->take(8);

        if ($words->isEmpty()) {
            return collect();
        }

        $query = KnowledgeBaseArticle::query()->published()->with('kbCategory');

        $query->where(function ($q) use ($words) {
            foreach ($words as $word) {
                $q->orWhere('title', 'like', "%{$word}%")
                    ->orWhere('content', 'like', "%{$word}%");
            }
        });

        if ($ticketCategoryId) {
            // Artikel dari kategori ticket yang sama diprioritaskan, tapi tidak memblokir hasil lain.
            $query->orderByRaw('CASE WHEN category_id = ? THEN 0 ELSE 1 END', [$ticketCategoryId]);
        }

        return $query->orderByDesc('view_count')->limit($limit)->get();
    }

    private function generateSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;

        while (
            KnowledgeBaseArticle::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}