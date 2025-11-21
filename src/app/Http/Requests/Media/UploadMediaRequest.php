<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for handling media uploads.
 *
 * @group Media
 */
class UploadMediaRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,gif,webp,mp4,webm', 'max:10240'],
            'context' => ['nullable', 'string', Rule::in(['activities', 'pet_avatars', 'general'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => __('media.validation.file.required'),
            'file.file' => __('media.validation.file.file'),
            'file.mimes' => __('media.validation.file.mimes'),
            'file.max' => __('media.validation.file.max'),
            'context.in' => __('media.validation.context.in'),
        ];
    }

    /**
     * Get body parameters for API documentation.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        $examplePath = base_path('docs/examples/pet-photo.png');

        return [
            'file' => [
                'description' => 'Binary file to upload (images and short video clips supported).',
                'example' => $examplePath,
            ],
            'context' => [
                'description' => 'Optional storage context to group files (activities, pet_avatars, general).',
                'example' => 'activities',
            ],
        ];
    }
}
