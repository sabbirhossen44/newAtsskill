<?php

namespace App\Http\Requests\Global;

use Illuminate\Foundation\Http\FormRequest;

class CloudStorageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '_token' => 'required',
            'file' => 'required',
            'source' => 'required',
        ];
    }
}
