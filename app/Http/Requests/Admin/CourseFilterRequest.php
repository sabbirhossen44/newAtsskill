<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CourseFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'category' => ['sometimes', 'integer', 'exists:course_categories,id'],
            'title' => ['required', 'string', 'max:255']
        ];

        return $rules;
    }
}
