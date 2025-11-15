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

    /**
     * Document body parameters for Scribe.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'credit_bundle_id' => [
                'description' => 'The ID of the credit bundle to purchase.',
                'example' => '3c0a0e3c-1234-4b9a-8123-9a7a2f1a9abc',
            ],
            'return_url' => [
                'description' => 'URL to redirect after checkout completes or cancels.',
                'example' => 'https://app.example.com/credits/return',
            ],
        ];
    }
}
