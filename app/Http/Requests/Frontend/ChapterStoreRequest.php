<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class ChapterStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('Title is required'),
            'title.max' => __('Title is too long'),
        ];
    }
}
