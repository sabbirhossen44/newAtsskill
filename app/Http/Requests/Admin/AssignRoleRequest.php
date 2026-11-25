<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:admins,id',
            'role' => 'required|array',
            'role.*' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => __('You must select an admin'),
            'user_id.exists' => __('Admin not found'),
            'role.required' => __('You must select role'),
            'role.array' => __('You must select role'),
            'role.*.required' => __('You must select role'),
            'role.*.string' => __('You must select role'),
        ];
    }
}
