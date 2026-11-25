<?php

namespace App\Http\Requests\Auth;

use App\Services\SettingService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for the custom (forget_password_token) reset form,
 * as previously validated inline in
 * NewPasswordController::custom_reset_password_store.
 */
class CustomResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $setting = (object) SettingService::all();

        return [
            'email' => 'required',
            'password' => 'required|min:4|confirmed',
            'g-recaptcha-response' => $setting->recaptcha_status == 'active' ? 'required' : '',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Email is required'),
            'password.required' => __('Password is required'),
            'password.min' => __('Password must be 4 characters'),
            'g-recaptcha-response.required' => __('Please complete the recaptcha to submit the form'),
        ];
    }
}
