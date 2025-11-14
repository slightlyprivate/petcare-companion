<?php

namespace App\Http\Requests\Gift;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for creating a new gift.
 *
 * @group Gifts
 */
class StoreGiftRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cost_in_credits' => ['required', 'integer', 'min:10', 'max:1000000', new \App\Rules\SufficientWalletBalance($this->user())],
            'return_url' => ['required', 'url'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cost_in_credits.required' => 'Gift cost in credits is required.',
            'cost_in_credits.integer' => 'Gift cost must be an integer.',
            'cost_in_credits.min' => 'Minimum gift cost is 10 credits.',
            'cost_in_credits.max' => 'Maximum gift cost is 1,000,000 credits.',
            'return_url.required' => 'Return URL is required.',
            'return_url.url' => 'Return URL must be a valid URL.',
        ];
    }

    /**
     * Get body parameters for API documentation.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'cost_in_credits' => [
                'description' => 'The gift cost in credits (10 - 1000000).',
                'example' => 100,
            ],
            'return_url' => [
                'description' => 'The URL to redirect the user after payment completion.',
                'example' => 'https://yourapp.com/gifts/success',
            ],
        ];
    }
}
