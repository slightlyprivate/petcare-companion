<?php

namespace App\Http\Requests\Pet;

/**
 * Request class for updating a pet.
 *
 * @group Pets
 */
class UpdatePetRequest extends \Illuminate\Foundation\Http\FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'species' => ['sometimes', 'required', 'string', 'max:100'],
            'breed' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today', 'after:1900-01-01'],
            'owner_name' => ['sometimes', 'required', 'string', 'max:255'],
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
     * Body parameters for API docs.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Pet name.',
                'example' => 'Buddy',
            ],
            'species' => [
                'description' => 'Species of the pet.',
                'example' => 'Dog',
            ],
            'breed' => [
                'description' => 'Breed of the pet (optional).',
                'example' => 'Labrador Retriever',
            ],
            'birth_date' => [
                'description' => 'Birth date (YYYY-MM-DD).',
                'example' => '2020-01-15',
            ],
            'owner_name' => [
                'description' => 'Owner full name.',
                'example' => 'Alex Doe',
            ],
        ];
    }
}
