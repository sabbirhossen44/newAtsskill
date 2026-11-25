<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'max:255'],
            'url' => ['nullable', 'max:255'],
            'status' => ['required'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
