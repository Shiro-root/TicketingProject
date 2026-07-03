<?php

namespace App\Http\Requests\Ticket;

use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('ticket.create');
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'category_id' => ['required', 'exists:categories,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'priority' => ['required', 'in:'.implode(',', TicketPriority::values())],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
            'asset_ids' => ['nullable', 'array'],
            'asset_ids.*' => ['exists:assets,id'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,docx,xlsx,zip'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Judul ticket wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'description.min' => 'Deskripsi minimal 10 karakter agar teknisi memahami masalahnya.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'attachments.*.mimes' => 'Lampiran harus berformat JPG, PNG, PDF, DOCX, XLSX, atau ZIP.',
            'attachments.*.max' => 'Ukuran lampiran maksimal 10MB.',
        ];
    }
}
