<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CourseReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::guard('admin')->check();
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
