<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class InstructorCourseDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => __('message is required'),
            'message.max' => __('message should not be more than 1000 characters'),
        ];
    }
}
