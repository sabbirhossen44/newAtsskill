<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CounterSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:255'],
            'total_student_count' => ['nullable', 'numeric', 'min:0'],
            'total_instructor_count' => ['nullable', 'numeric', 'min:0'],
            'total_courses_count' => ['nullable', 'numeric', 'min:0'],
            'total_awards_count' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable'],
        ];
    }
}
