<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required',
            'password' => 'required|min:4|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Email is required'),
            'password.required' => __('Password is required'),
            'password.min' => __('Password must be 4 characters'),
        ];
    }
}
