<?php

namespace App\Http\Requests\Ticket;

use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ticket'));
    }

    public function rules(): array
    {
        return [
            'subject' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string', 'min:10'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'priority' => ['sometimes', 'required', 'in:'.implode(',', TicketPriority::values())],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'asset_ids' => ['nullable', 'array'],
            'asset_ids.*' => ['exists:assets,id'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,docx,xlsx,zip'],
        ];
    }
}
