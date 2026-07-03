<?php

namespace App\Policies;

use App\Models\KnowledgeBaseArticle;
use App\Models\User;

/**
 * Super Admin selalu bypass lewat Gate::before di AppServiceProvider.
 * Baca artikel published boleh siapa saja yang login (KB adalah shared
 * knowledge internal); menulis/mengubah/menghapus butuh permission kb.manage.
 */
class KnowledgeBaseArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('kb.view') || $user->hasPermission('kb.manage');
    }

    public function view(User $user, KnowledgeBaseArticle $article): bool
    {
        if ($article->is_published) {
            return $user->hasPermission('kb.view') || $user->hasPermission('kb.manage');
        }

        // Draft (belum publish) hanya boleh dilihat penulis atau yang punya kb.manage.
        return $user->hasPermission('kb.manage') || $article->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('kb.manage');
    }

    public function update(User $user, KnowledgeBaseArticle $article): bool
    {
        return $user->hasPermission('kb.manage');
    }

    public function delete(User $user, KnowledgeBaseArticle $article): bool
    {
        return $user->hasPermission('kb.manage');
    }

    public function restore(User $user, KnowledgeBaseArticle $article): bool
    {
        return $user->hasPermission('kb.manage');
    }
}