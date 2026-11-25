<?php

namespace App\Http\Requests\Frontend;

use Illuminate\Foundation\Http\FormRequest;

class BkashRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paymentID' => 'required|string',
            'trxID' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ];
    }
}
