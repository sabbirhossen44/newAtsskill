<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CourseCategoryUpdateRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'image', 'max:255'],
            'status' => ['nullable'],
            'code' => ['nullable', 'exists:languages,code'],
            'parent_id' => ['nullable', 'exists:course_categories,id'],
        ];
    }
}
