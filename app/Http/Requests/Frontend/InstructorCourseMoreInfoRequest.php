<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class InstructorCourseMoreInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_duration' => ['required', 'numeric', 'min:0'],
            'category' => ['required'],
        ];
    }
}
