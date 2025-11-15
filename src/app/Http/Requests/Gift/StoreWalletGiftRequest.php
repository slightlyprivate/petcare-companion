<?php

namespace App\Http\Requests\Gift;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for storing a wallet gift.
 *
 * @group Gifts
 */
class StoreWalletGiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pet_id' => ['required', 'integer', 'exists:pets,id'],
            'gift_type_id' => ['required', 'uuid', 'exists:gift_types,id'],
            'cost_in_credits' => ['required', 'integer', 'min:10', 'max:1000000', new \App\Rules\SufficientWalletBalance($this->user())],
        ];
    }

    public function messages(): array
    {
        return [
            'pet_id.required' => 'Pet ID is required.',
            'pet_id.integer' => 'Pet ID must be a valid integer.',
            'pet_id.exists' => 'Selected pet does not exist.',
            'gift_type_id.required' => 'Gift type is required.',
            'gift_type_id.uuid' => 'Gift type must be a valid ID.',
            'gift_type_id.exists' => 'Selected gift type does not exist.',
            'cost_in_credits.required' => 'Gift cost in credits is required.',
            'cost_in_credits.integer' => 'Gift cost must be an integer.',
            'cost_in_credits.min' => 'Minimum gift cost is 10 credits.',
            'cost_in_credits.max' => 'Maximum gift cost is 1,000,000 credits.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'pet_id' => [
                'description' => 'The ID of the pet to receive the gift.',
                'example' => 1,
            ],
            'gift_type_id' => [
                'description' => 'Catalog gift type to associate with this gift.',
                'example' => 'a3f0e2b4-7f2a-4c1d-8a17-3a1d9c123456',
            ],
            'cost_in_credits' => [
                'description' => 'Gift cost in credits to deduct from the wallet.',
                'example' => 100,
            ],
        ];
    }
}
