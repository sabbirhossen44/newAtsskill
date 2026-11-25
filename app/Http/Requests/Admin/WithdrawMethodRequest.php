<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'minimum_amount' => ['required', 'numeric'],
            'maximum_amount' => ['nullable', 'numeric'],
            'description' => ['required', 'string'],
            'status' => ['nullable'],
        ];
    }
}
