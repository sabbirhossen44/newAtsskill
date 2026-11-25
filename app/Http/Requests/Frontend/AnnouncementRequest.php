<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course' => 'required',
            'title' => ['required', 'max:255'],
            'announcement' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'course.required' => 'Course is required',
            'announcement.required' => 'Announcement is required',
            'title.required' => 'Title is required',
            'title.max' => 'Title should not be more than 255 characters',
        ];
    }
}
