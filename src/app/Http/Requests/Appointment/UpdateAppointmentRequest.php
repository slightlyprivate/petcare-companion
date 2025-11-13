<?php

namespace App\Http\Requests\Appointment;

/**
 * Request class for updating an appointment.
 *
 * @group Appointments
 */
class UpdateAppointmentRequest extends \Illuminate\Foundation\Http\FormRequest
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
            'pet_id' => ['sometimes', 'required', 'integer', 'exists:pets,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'scheduled_at' => ['sometimes', 'required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'pet_id.required' => 'Pet selection is required.',
            'pet_id.exists' => 'Selected pet does not exist.',
            'title.required' => 'Appointment title is required.',
            'scheduled_at.required' => 'Appointment date and time is required.',
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
            'pet_id' => [
                'description' => 'The ID of the pet for this appointment (optional if updating existing).',
                'example' => 1,
            ],
            'title' => [
                'description' => 'The title of the appointment (optional if updating existing).',
                'example' => 'Vet Checkup',
            ],
            'scheduled_at' => [
                'description' => 'The scheduled date and time for the appointment in ISO 8601 format (optional if updating existing).',
                'example' => '2024-12-15T14:30:00Z',
            ],
            'notes' => [
                'description' => 'Additional notes about the appointment (optional).',
                'example' => 'Annual vaccination due',
            ],
        ];
    }
}
