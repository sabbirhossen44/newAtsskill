<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for the student/user login form, as previously
 * validated inline in AuthenticatedSessionController::store.
 * The credential, status, banned and email-verified checks stay
 * in the controller; this request only carries the rules.
 */
class StudentLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
            // 'g-recaptcha-response' => $setting->recaptcha_status == 'active' ? 'required' : 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Email is required'),
            'password.required' => __('Password is required'),
            // 'g-recaptcha-response.required' => __('Please complete the recaptcha to submit the form'),
        ];
    }
}
