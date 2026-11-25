<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class QuizQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'max:255'],
            'answers.*' => ['required', 'max:255'],
            'grade' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('Question title is required'),
            'title.max' => __('Question title should not be more than 255 characters'),
            'answers.*.required' => __('At least one answer is required'),
            'answers.*.max' => __('Answer should not be more than 255 characters'),
            'grade.required' => __('Grade is required'),
            'grade.numeric' => __('Grade should be a number'),
            'grade.min' => __('Grade should be greater than or equal to 0'),
        ];
    }
}
