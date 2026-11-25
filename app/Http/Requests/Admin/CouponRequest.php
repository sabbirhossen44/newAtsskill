<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return \Illuminate\Support\Facades\Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'numeric'],
            'discount' => ['nullable', 'numeric'],
            'expired_date' => ['required', 'date'],
            'minimum_order_amount' => ['nullable', 'numeric'],
            'usage_limit' => ['nullable', 'numeric'],
            'type' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
        ];
    }
}
