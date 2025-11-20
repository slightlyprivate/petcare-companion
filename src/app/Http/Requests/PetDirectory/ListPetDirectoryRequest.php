<?php

namespace App\Http\Requests\PetDirectory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for listing pets in the directory.
 *
 * @group Pets
 */
class ListPetDirectoryRequest extends FormRequest
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
            'species' => ['sometimes', 'string', 'max:100'],
            'owner_name' => ['sometimes', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:255'],
            'sort_by' => ['sometimes', 'string', 'in:name,species,breed,owner_name,birth_date,created_at,popularity'],
            'sort_direction' => ['sometimes', 'string', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get query parameters for API documentation.
     *
     * @return array<string, array<string, mixed>>
     */
    public function queryParameters(): array
    {
        return [
            'species' => [
                'description' => 'Filter pets by species.',
                'example' => 'Dog',
            ],
            'owner_name' => [
                'description' => 'Filter pets by owner name.',
                'example' => 'John Doe',
            ],
            'name' => [
                'description' => 'Filter pets by name.',
                'example' => 'Buddy',
            ],
            'sort_by' => [
                'description' => 'Field to sort by (popularity sorts by total gifts, descending by default).',
                'example' => 'popularity',
            ],
            'sort_direction' => [
                'description' => 'Sort direction (asc or desc).',
                'example' => 'desc',
            ],
            'per_page' => [
                'description' => 'Number of items per page (1-100).',
                'example' => 15,
            ],
        ];
    }
}
