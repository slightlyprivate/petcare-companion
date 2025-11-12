<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for creating a new donation.
 *
 * @group Donations
 */
class DonationStoreRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:1', 'max:10000'],
            'return_url' => ['required', 'url'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Donation amount is required.',
            'amount.numeric' => 'Donation amount must be a number.',
            'amount.min' => 'Minimum donation amount is $1.00.',
            'amount.max' => 'Maximum donation amount is $10,000.00.',
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
            'amount' => [
                'description' => 'The donation amount in dollars (1.00 - 10000.00).',
                'example' => 25.00,
            ],
            'return_url' => [
                'description' => 'The URL to redirect the user after payment completion.',
                'example' => 'https://yourapp.com/donations/success',
            ],
        ];
    }
}
