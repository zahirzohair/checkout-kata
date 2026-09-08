<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScanItemRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9\-]+$/', Rule::exists('sku_pricings', 'sku')],
        ];
    }
}
