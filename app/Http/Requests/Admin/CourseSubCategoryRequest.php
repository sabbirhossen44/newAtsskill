<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CourseSubCategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules =  [
            'name' => ['required', 'string', 'max:255'],
        ];
        if($this->isMethod('post')) {
            $rules += [
                'slug' => ['nullable', 'string', 'max:255', 'unique:course_categories,slug'],
                'status' => ['required'],
            ];
        }
        if($this->isMethod('put')) {
            $rules['code'] = 'nullable|exists:languages,code';
        }
        return $rules;
    }

    function messages(): array
    {
        return [
            'name.required' => __('The name field is required'),
            'name.string' => __('The name must be a string'),
            'name.max' => __('The name may not be greater than 255 characters.'),
            'slug.required' => __('The slug field is required'),
            'slug.unique' => __('The slug has already been taken.'),
            'status.required' => __('The status field is required'),
            'status.boolean' => __('The status must be a boolean'),
            'code.required' => __('Language code is required.'),
        ];
    }
}
