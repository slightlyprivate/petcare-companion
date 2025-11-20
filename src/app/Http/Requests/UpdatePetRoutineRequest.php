<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for updating an existing pet routine.
 *
 * @group Pets
 */
class UpdatePetRoutineRequest extends FormRequest
{
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
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'time_of_day' => ['sometimes', 'date_format:H:i'],
            'days_of_week' => ['sometimes', 'array', 'min:1'],
            'days_of_week.*' => ['integer', Rule::in([0, 1, 2, 3, 4, 5, 6])],
        ];
    }

    /**
     * Document body parameters for API generation.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Display name of the routine.',
                'example' => 'Evening Walk',
            ],
            'description' => [
                'description' => 'Optional longer description.',
                'example' => '30-minute walk around the neighborhood.',
            ],
            'time_of_day' => [
                'description' => 'Time of day in 24h format (HH:MM).',
                'example' => '18:00',
            ],
            'days_of_week' => [
                'description' => 'Array of weekday indices (0=Sun .. 6=Sat).',
                'example' => [0, 2, 4],
            ],
        ];
    }
}
