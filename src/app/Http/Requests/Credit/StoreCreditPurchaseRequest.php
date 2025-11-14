<?php

namespace App\Http\Requests\Credit;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for storing a credit purchase.
 */
class StoreCreditPurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'credit_bundle_id' => [
                'required',
                'uuid',
                'exists:credit_bundles,id,is_active,1',
            ],
            'return_url' => [
                'required',
                'url',
                'max:2048',
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'credit_bundle_id.exists' => __('credits.errors.invalid_bundle'),
            'return_url.url' => __('credits.errors.invalid_return_url'),
        ];
    }
}
