<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class TinymceImageUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => __('The image is required and must be an image file with a maximum size of 2048 kilobytes (2 MB).'),
            'file.image' => __('The image must be an image file with a maximum size of 2048 kilobytes (2 MB).'),
            'file.max' => __('The image must be an image file with a maximum size of 2048 kilobytes (2 MB).'),
        ];
    }

    /**
     * The old controller validated manually and returned a 422 JSON
     * payload with the first error under an `error` key; keep that
     * exact response shape instead of the default redirect/errors bag.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json(['error' => $validator->errors()->first()], 422));
    }
}
