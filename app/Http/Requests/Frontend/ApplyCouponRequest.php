<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coupon' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'coupon.required' => __('Coupon is required'),
        ];
    }
}
