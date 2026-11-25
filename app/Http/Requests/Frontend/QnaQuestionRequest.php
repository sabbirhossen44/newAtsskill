<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class QnaQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => ['required', 'max:255'],
            'description' => ['required'],
            'g-recaptcha-response' => setting('recaptcha_status') === 'active' ? 'required' : 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => __('Question is required'),
            'question.max' => __('Question may not be greater than 255 characters'),
            'description.required' => __('Description is required'),
            'g-recaptcha-response.required' => __('Please complete the recaptcha to submit the form'),
        ];
    }
}
