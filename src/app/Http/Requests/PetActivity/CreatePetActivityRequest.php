<?php

namespace App\Http\Requests\PetActivity;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for creating a new pet activity.
 *
 * @group Pets
 */
class CreatePetActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Policy enforcement will occur in controller later.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string'],
            'media_url' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => __('activity.validation.type.required'),
            'type.max' => __('activity.validation.type.max'),
            'description.required' => __('activity.validation.description.required'),
            'media_url.max' => __('activity.validation.media_url.max'),
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
            'type' => [
                'description' => 'Short classification for the activity (e.g., feeding, walk, vet).',
                'example' => 'feeding',
            ],
            'description' => [
                'description' => 'Detailed description of what happened.',
                'example' => 'Fed 1 cup of dry food and fresh water.',
            ],
            'media_url' => [
                'description' => 'Optional media reference. Can be a full URL or a path returned from the upload endpoint.',
                'example' => 'activities/media/feeding.jpg',
            ],
        ];
    }
}
