<?php

namespace App\Http\Requests\KnowledgeBase;

use App\Models\KnowledgeBaseArticle;
use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', KnowledgeBaseArticle::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'knowledge_base_category_id' => ['required', 'exists:knowledge_base_categories,id'],
            'category_id' => ['nullable', 'exists:categories,id'], // link ke kategori ticket, dipakai AI Suggested Solution
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'min:20'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul artikel wajib diisi.',
            'knowledge_base_category_id.required' => 'Kategori KB wajib dipilih.',
            'content.required' => 'Isi artikel wajib diisi.',
            'content.min' => 'Isi artikel minimal 20 karakter.',
        ];
    }
}