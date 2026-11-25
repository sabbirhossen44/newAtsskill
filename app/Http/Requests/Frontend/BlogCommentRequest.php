<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class BlogCommentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'comment' => ['required', 'max:1000'],
            'g-recaptcha-response' => setting('recaptcha_status') == 'active' ? 'required' : 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'comment.required' => __('The comment field is required'),
            'comment.max' => __('The comment must not be greater than 1000 characters'),
            'g-recaptcha-response.required' => __('The reCAPTCHA verification is required'),
            'g-recaptcha-response.recaptcha' => __('The reCAPTCHA verification failed'),
        ];
    }
}
