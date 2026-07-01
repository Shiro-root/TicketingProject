<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'color'];

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class, 'ticket_tag')->withTimestamps();
    }

    public function knowledgeBaseArticles(): BelongsToMany
    {
        return $this->belongsToMany(
            KnowledgeBaseArticle::class,
            'knowledge_base_article_tag'
        )->withTimestamps();
    }
}
