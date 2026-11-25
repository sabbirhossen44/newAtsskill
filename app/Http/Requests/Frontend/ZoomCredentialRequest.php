<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class ZoomCredentialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => 'required',
            'client_secret' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => __('Client ID is required'),
            'client_secret.required' => __('Client secret is required'),
        ];
    }
}
