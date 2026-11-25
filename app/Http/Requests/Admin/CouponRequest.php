<?php

namespace App\Http\Requests\Admin;

use App\Enums\CouponStatusEnum;
use App\Enums\CouponTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'type' => ['nullable', 'string', Rule::in(array_column(CouponTypeEnum::cases(), 'value'))],
            'status' => ['nullable', 'string', Rule::in(array_column(CouponStatusEnum::cases(), 'value'))],
        ];
    }
}
