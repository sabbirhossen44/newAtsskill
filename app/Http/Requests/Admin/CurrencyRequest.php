<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255'],
            'symbol' => ['nullable', 'string', 'max:255'],
            'exchange_rate' => ['required', 'numeric'],
            'status' => ['nullable'],
        ];
    }
}
