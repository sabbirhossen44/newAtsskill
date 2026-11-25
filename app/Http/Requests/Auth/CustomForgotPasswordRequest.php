<?php

namespace App\Http\Requests\Auth;

use App\Services\SettingService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for the custom forget-password form, as previously
 * validated inline in PasswordResetLinkController::custom_forget_password.
 */
class CustomForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $setting = (object) SettingService::all();

        return [
            'email' => ['required', 'email'],
            'g-recaptcha-response' => $setting->recaptcha_status == 'active' ? 'required' : '',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Email is required'),
            'g-recaptcha-response.required' => __('Please complete the recaptcha to submit the form'),
        ];
    }
}
