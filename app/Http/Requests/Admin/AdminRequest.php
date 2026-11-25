<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $adminId = $this->route('admin');

        if ($this->isMethod('post')) {
            return [
                'name' => 'required',
                'email' => 'required|unique:admins',
                'password' => 'required|min:4',
                'status' => 'required',
                'role' => 'nullable|array',
            ];
        }

        return [
            'name' => 'required',
            'email' => ['required', Rule::unique('admins', 'email')->ignore($adminId)],
            'password' => 'nullable|min:4',
            'status' => 'required',
            'role' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Name is required'),
            'email.required' => __('Email is required'),
            'status.required' => __('Status is required'),
            'email.unique' => __('Email already exist'),
            'password.required' => __('Password is required'),
            'password.min' => __('Password Must be 4 characters'),
            'role.array' => __('You must select role'),
        ];
    }
}
