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
            'gift_type_id' => ['required', 'uuid', 'exists:gift_types,id'],
            'cost_in_credits' => ['required', 'integer', 'min:10', 'max:1000000', new \App\Rules\SufficientWalletBalance($this->user())],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'gift_type_id.required' => 'Gift type is required.',
            'gift_type_id.uuid' => 'Gift type must be a valid ID.',
            'gift_type_id.exists' => 'Selected gift type does not exist.',
            'cost_in_credits.required' => 'Gift cost in credits is required.',
            'cost_in_credits.integer' => 'Gift cost must be an integer.',
            'cost_in_credits.min' => 'Minimum gift cost is 10 credits.',
            'cost_in_credits.max' => 'Maximum gift cost is 1,000,000 credits.',
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
            'gift_type_id' => [
                'description' => 'The catalog gift type ID to associate with this gift.',
                'example' => 'a3f0e2b4-7f2a-4c1d-8a17-3a1d9c123456',
            ],
            'cost_in_credits' => [
                'description' => 'The gift cost in credits (10 - 1000000).',
                'example' => 100,
            ],
        ];
    }
}
