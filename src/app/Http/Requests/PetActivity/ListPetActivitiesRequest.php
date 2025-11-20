<?php

namespace App\Http\Requests\PetActivity;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for listing pet activities with pagination and optional filters.
 *
 * @group Pets
 */
class ListPetActivitiesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Policy will be applied in controller/service layer.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'type' => ['sometimes', 'string', 'max:50'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
        ];
    }

    /**
     * Custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'per_page.integer' => __('activity.validation.per_page.integer'),
            'per_page.min' => __('activity.validation.per_page.min'),
            'per_page.max' => __('activity.validation.per_page.max'),
            'type.max' => __('activity.validation.type.max'),
            'date_from.date' => __('activity.validation.date_from.date'),
            'date_to.date' => __('activity.validation.date_to.date'),
            'date_to.after_or_equal' => __('activity.validation.date_to.after_or_equal'),
        ];
    }

    /**
     * Query parameters for API documentation.
     *
     * @return array<string, array<string, mixed>>
     */
    public function queryParameters(): array
    {
        return [
            'per_page' => [
                'description' => 'Number of activities per page (1-100).',
                'example' => 15,
            ],
            'type' => [
                'description' => 'Filter by activity type (e.g., feeding, walk).',
                'example' => 'feeding',
            ],
            'date_from' => [
                'description' => 'Filter activities created on or after this date (YYYY-MM-DD).',
                'example' => '2025-11-01',
            ],
            'date_to' => [
                'description' => 'Filter activities created on or before this date (YYYY-MM-DD).',
                'example' => '2025-11-19',
            ],
        ];
    }
}
