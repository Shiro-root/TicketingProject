<?php

namespace App\Providers;

use App\Models\KnowledgeBaseArticle;
use App\Models\Ticket;
use App\Policies\KnowledgeBaseArticlePolicy;
use App\Policies\TicketPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(KnowledgeBaseArticle::class, KnowledgeBaseArticlePolicy::class);
    }
}