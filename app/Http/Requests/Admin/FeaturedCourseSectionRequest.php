<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class FeaturedCourseSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'all_category_ids' => ['nullable', 'array'],
            'category_one_ids' => ['nullable', 'array'],
            'category_two_ids' => ['nullable', 'array'],
            'category_three_ids' => ['nullable', 'array'],
            'category_four_ids' => ['nullable', 'array'],
            'category_five_ids' => ['nullable', 'array'],
            'status' => ['nullable'],
        ];
    }
}
