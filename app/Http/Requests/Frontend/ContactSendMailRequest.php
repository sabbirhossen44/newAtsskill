<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class ContactSendMailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:1000'],
            'g-recaptcha-response' => setting('recaptcha_status') == 'active' ? 'required' : 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Name is required'),
            'email.required' => __('Email is required'),
            'subject.required' => __('Subject is required'),
            'message.required' => __('Message is required'),
            'name.string' => __('Name must be a string'),
            'email.string' => __('Email must be a string'),
            'subject.string' => __('Subject must be a string'),
            'message.string' => __('Message must be a string'),
            'name.max' => __('Name may not be greater than 255 characters'),
            'email.max' => __('Email may not be greater than 255 characters'),
            'subject.max' => __('Subject may not be greater than 255 characters'),
            'message.max' => __('Message may not be greater than 1000 characters'),
            'g-recaptcha-response.required' => __('Please complete the recaptcha to submit the form'),
        ];
    }
}
