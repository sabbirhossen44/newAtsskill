<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class QnaReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reply' => ['required', 'string'],
            'g-recaptcha-response' => setting('recaptcha_status') === 'active' ? 'required' : 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'reply.required' => __('Replay is required'),
            'g-recaptcha-response.required' => __('Please complete the recaptcha to submit the form'),
        ];
    }
}
