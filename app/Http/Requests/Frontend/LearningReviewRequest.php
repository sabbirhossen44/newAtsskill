<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class LearningReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'exists:courses,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['required', 'max: 1000', 'string'],
            'g-recaptcha-response' => setting('recaptcha_status') === 'active' ? 'required' : 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => __('rating filed is required'),
            'rating.integer' => __('rating have to be an integer'),
            'review.required' => __('review filed is required'),
            'g-recaptcha-response.required' => __('Please complete the recaptcha to submit the form'),
        ];
    }
}
