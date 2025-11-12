<?php

namespace App\Http\Requests;

/**
 * Request class for storing a new pet.
 *
 * @group Pets
 */
class PetStoreRequest extends \Illuminate\Foundation\Http\FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'species' => ['required', 'string', 'max:100'],
            'breed' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today', 'after:1900-01-01'],
            'owner_name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Pet name is required.',
            'species.required' => 'Pet species is required.',
            'owner_name.required' => 'Owner name is required.',
            'birth_date.before_or_equal' => 'Birth date cannot be in the future.',
            'birth_date.after' => 'Birth date must be after 1900.',
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
            'name' => [
                'description' => 'The name of the pet.',
                'example' => 'Buddy',
            ],
            'species' => [
                'description' => 'The species of the pet (e.g., Dog, Cat, Bird).',
                'example' => 'Dog',
            ],
            'breed' => [
                'description' => 'The breed of the pet (optional).',
                'example' => 'Golden Retriever',
            ],
            'birth_date' => [
                'description' => 'The birth date of the pet in YYYY-MM-DD format (optional).',
                'example' => '2020-05-15',
            ],
            'owner_name' => [
                'description' => 'The name of the pet owner.',
                'example' => 'John Doe',
            ],
        ];
    }
}
