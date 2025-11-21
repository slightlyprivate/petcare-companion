<?php

namespace App\Http\Requests\Pet;

use App\Models\Pet;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for uploading a pet avatar.
 */
class PetAvatarUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Pet|null $pet */
        $pet = $this->route('pet');

        return $pet ? $this->user()->can('update', $pet) : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp,gif', 'max:5120'], // max 5MB
        ];
    }

    /**
     * Provide body parameters for API documentation.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        $examplePath = base_path('docs/examples/pet-photo.png');

        return [
            'avatar' => [
                'description' => 'Avatar image to represent the pet (JPEG, PNG, WEBP, or GIF up to 5MB).',
                'example' => $examplePath,
            ],
        ];
    }
}
