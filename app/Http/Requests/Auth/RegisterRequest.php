<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for user registration, as previously validated
 * inline in RegisteredUserController::store.
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['required'],
            // 'g-recaptcha-response' => $setting->recaptcha_status == 'active' ? 'required' : '',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Name is required'),
            'email.required' => __('Email is required'),
            'email.unique' => __('Email already exist'),
            'phone.required' => __('Phone number is required'),
            'password.required' => __('Password is required'),
            'password.confirmed' => __('Confirm password does not match'),
            'terms.required' => __('You must agree to the terms and conditions'),
            // 'g-recaptcha-response.required' => __('Please complete the recaptcha to submit the form'),
        ];
    }
}
