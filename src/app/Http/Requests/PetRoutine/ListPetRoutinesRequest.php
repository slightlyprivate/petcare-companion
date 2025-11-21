<?php

namespace App\Http\Requests\PetRoutine;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for listing pet routines with optional day filter.
 *
 * @group Pets
 */
class ListPetRoutinesRequest extends FormRequest
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
            'day' => ['sometimes', 'integer', 'between:0,6'],
        ];
    }

    /**
     * @return array<string, array<string,mixed>>
     */
    public function queryParameters(): array
    {
        return [
            'day' => [
                'description' => 'Filter routines active on this weekday (0=Sun .. 6=Sat).',
                'example' => 1,
            ],
        ];
    }
}
