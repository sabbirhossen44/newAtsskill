<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class NewsletterSubscribeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:news_letters,email',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => __('Email is required'),
            'email.unique' => __('Email already exist'),
        ];
    }
}
