<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for storing a new pet.
 */
class PetStoreRequest extends FormRequest
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
}
