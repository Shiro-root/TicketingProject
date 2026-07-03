<?php

namespace App\Http\Requests\Asset;

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('asset.manage');
    }

    public function rules(): array
    {
        return [
            'asset_tag' => ['required', 'string', 'max:50', 'unique:assets,asset_tag'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:'.implode(',', array_map(fn ($t) => $t->value, AssetType::cases()))],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100', 'unique:assets,serial_number'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:'.implode(',', array_map(fn ($s) => $s->value, AssetStatus::cases()))],
            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'warranty_expiry' => ['nullable', 'date', 'after_or_equal:purchase_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'asset_tag.required' => 'Asset Tag wajib diisi.',
            'asset_tag.unique' => 'Asset Tag sudah digunakan.',
            'serial_number.unique' => 'Serial Number sudah terdaftar untuk asset lain.',
            'warranty_expiry.after_or_equal' => 'Tanggal berakhir garansi tidak boleh sebelum tanggal pembelian.',
        ];
    }
}