<?php

namespace App\Http\Requests;

use App\Models\Bill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $this->user()?->id)),
            ],
            'title' => ['required', 'string', 'max:255'],
            'amount_cents' => ['required', 'integer', 'min:0'],
            'due_date' => ['required', 'date'],
            'status' => ['sometimes', Rule::in([Bill::STATUS_OPEN, Bill::STATUS_PAID])],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
