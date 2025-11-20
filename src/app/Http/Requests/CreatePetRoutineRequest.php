<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for creating a new routine for a pet.
 *
 * @group Pets
 */
class CreatePetRoutineRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            // Accept HH:MM or HH:MM:SS by normalizing via regex; enforce format explicitly.
            'time_of_day' => ['required', 'date_format:H:i'],
            'days_of_week' => ['required', 'array', 'min:1'],
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
                'example' => 'Morning Feeding',
            ],
            'description' => [
                'description' => 'Optional longer description.',
                'example' => 'Feed 1 cup dry food and fresh water.',
            ],
            'time_of_day' => [
                'description' => 'Time of day in 24h format (HH:MM).',
                'example' => '07:30',
            ],
            'days_of_week' => [
                'description' => 'Array of weekday indices (0=Sun .. 6=Sat).',
                'example' => [1, 2, 3, 4, 5],
            ],
        ];
    }
}
