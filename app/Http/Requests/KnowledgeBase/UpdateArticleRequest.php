<?php

namespace App\Http\Requests\KnowledgeBase;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('article'));
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'knowledge_base_category_id' => ['required', 'exists:knowledge_base_categories,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'min:20'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}