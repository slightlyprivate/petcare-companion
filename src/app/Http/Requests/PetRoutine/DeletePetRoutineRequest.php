<?php

namespace App\Http\Requests\PetRoutine;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request class for deleting a pet routine.
 *
 * @group Pets
 */
class DeletePetRoutineRequest extends FormRequest
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
            'reason' => ['sometimes', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, array<string,mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'reason' => [
                'description' => 'Optional reason for deleting the routine (for audit logs).',
                'example' => 'No longer needed',
            ],
        ];
    }
}
