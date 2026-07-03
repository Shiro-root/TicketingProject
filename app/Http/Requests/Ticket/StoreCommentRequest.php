<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Internal note hanya boleh dibuat oleh staff (bukan Employee/Guest).
        if ($this->boolean('is_internal') && $this->user()->hasRole('employee', 'guest')) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'min:1'],
            'parent_id' => ['nullable', 'exists:ticket_comments,id'],
            'is_internal' => ['sometimes', 'boolean'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,docx,xlsx,zip'],
        ];
    }
}
