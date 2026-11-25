<?php

namespace App\Http\Requests\Admin;

use App\Enums\InstructorRequestStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstructorRequestUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(array_column(InstructorRequestStatusEnum::cases(), 'value'))],
        ];
    }
}
