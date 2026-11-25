<?php

namespace App\Http\Requests\Frontend;

use App\Repositories\LiveCredentialRepository;
use Illuminate\Foundation\Http\FormRequest;

class JitsiCredentialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'app_id' => 'required',
            'api_key' => 'required',
            'permissions' => 'sometimes',
        ];

        // The RSA private key file is required only when none is stored yet.
        $extension = $this->hasFile('private_key') ? $this->file('private_key')->getClientOriginalExtension() : 'pk';
        if (! LiveCredentialRepository::privateKeyExists(auth('web')->user()->id, $extension)) {
            $rules['private_key'] = 'required|file';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'app_id.required' => __('App ID is required'),
            'api_key.required' => __('API Key is required'),
            'private_key.required' => __('RSA Private key file is required'),
            'private_key.file' => __('RSA Private key must be a valid file'),
        ];
    }
}
