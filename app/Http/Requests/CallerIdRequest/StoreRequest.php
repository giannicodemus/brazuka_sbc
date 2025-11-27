<?php

namespace App\Http\Requests\CallerIdRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'clids' => 'required|array|min:1',
            'clids.*.order' => 'required|integer|min:1',
            'clids.*.number' => 'required|integer',
        ];
    }
}
