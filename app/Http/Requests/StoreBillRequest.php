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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => is_string($this->title) ? trim(strip_tags($this->title)) : $this->title,
            'notes' => is_string($this->notes) ? trim(strip_tags($this->notes)) : $this->notes,
        ]);
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $this->user()?->id)),
            ],
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'amount_cents' => ['required', 'integer', 'gt:0'],
            'due_date' => ['required', 'date'],
            'status' => ['sometimes', Rule::in([Bill::STATUS_OPEN, Bill::STATUS_PAID])],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Selecione uma categoria.',
            'category_id.exists' => 'A categoria informada não pertence ao seu usuário.',
            'title.required' => 'O título da conta é obrigatório.',
            'title.min' => 'O título da conta deve ter ao menos 2 caracteres.',
            'amount_cents.required' => 'O valor da conta é obrigatório.',
            'amount_cents.gt' => 'O valor da conta deve ser maior que zero.',
            'due_date.required' => 'A data de vencimento é obrigatória.',
            'due_date.date' => 'Informe uma data de vencimento válida.',
        ];
    }
}
