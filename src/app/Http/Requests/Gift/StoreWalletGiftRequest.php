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
            'gift_type_id' => [
                'required',
                'uuid',
                'exists:gift_types,id,is_active,1',
                new \App\Rules\SufficientWalletForGiftType($this->user()),
            ],
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
            'gift_type_id.exists' => 'Selected gift type does not exist or is inactive.',
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
        ];
    }
}
