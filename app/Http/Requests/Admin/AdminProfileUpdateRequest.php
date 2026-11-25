<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is enforced by the admin permission check in the controller
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'email' => ['required', Rule::unique('admins', 'email')->ignore(auth('admin')->id())],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Name is required'),
            'email.required' => __('Email is required'),
            'email.unique' => __('Email already exist'),
        ];
    }
}
