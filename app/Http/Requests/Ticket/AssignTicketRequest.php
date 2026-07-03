<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('ticket.assign');
    }

    public function rules(): array
    {
        return [
            'technician_id' => ['required', 'exists:users,id'],
            'is_lead' => ['sometimes', 'boolean'],
        ];
    }
}
