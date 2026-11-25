<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminPasswordUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth is enforced by the admin permission check in the controller
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required',
            'password' => 'required|confirmed|min:4',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => __('Current password is required'),
            'password.required' => __('Password is required'),
            'password.confirmed' => __('Confirm password does not match'),
            'password.min' => __('Password must be at leat 4 characters'),
        ];
    }
}
