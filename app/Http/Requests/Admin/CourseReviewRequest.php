<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CourseReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'review' => ['nullable', 'string'],
            'comment' => ['nullable', 'string'],
            'rating' => ['nullable', 'numeric'],
            'status' => ['nullable'],
        ];
    }
}
