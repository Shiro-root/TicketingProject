<?php

namespace App\Http\Controllers;

use App\Services\KnowledgeBaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint AJAX yang dipanggil dari tickets/_form.blade.php sambil user
 * mengetik subject/description — menampilkan artikel KB relevan supaya
 * user bisa menyelesaikan masalah sendiri sebelum membuat ticket.
 */
class KnowledgeBaseSuggestController extends Controller
{
    public function __invoke(Request $request, KnowledgeBaseService $kb): JsonResponse
    {
        $request->validate([
            'text' => ['required', 'string', 'min:6'],
            'category_id' => ['nullable', 'integer'],
        ]);

        $suggestions = $kb->suggest($request->string('text')->toString(), $request->integer('category_id') ?: null);

        return response()->json([
            'suggestions' => $suggestions->map(fn ($article) => [
                'title' => $article->title,
                'excerpt' => $article->excerpt,
                'category' => $article->kbCategory?->name,
                'url' => route('knowledge-base.show', $article),
            ])->values(),
        ]);
    }
}