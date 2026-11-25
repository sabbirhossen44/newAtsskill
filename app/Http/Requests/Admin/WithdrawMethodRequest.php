<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class WithdrawMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::guard('admin')->check();
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
