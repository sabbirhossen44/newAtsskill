<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InstructorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|max:255',
            'designation' => 'required|max:255',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'status' => 'nullable',
        ];

        if ($this->isMethod('post')) {
            $rules['name'] = 'required|max:255';
            $rules['designation'] = 'required|max:255';
            $rules['image'] = 'required|image|mimes:jpeg,png,jpg,webp|max:2048';
        } else {
            $rules['name'] = 'required|string|max:255';
            $rules['designation'] = 'required|string|max:255';
            $rules['image'] = 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048';
        }

        return $rules;
    }
}
