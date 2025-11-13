<?php

namespace App\Http\Requests\Appointment;

/**
 * Request class for storing a new appointment.
 *
 * @group Appointments
 */
class AppointmentStoreRequest extends \Illuminate\Foundation\Http\FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Appointment title is required.',
            'scheduled_at.required' => 'Appointment date and time is required.',
            'scheduled_at.after' => 'Appointment must be scheduled for a future date and time.',
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
            'title' => [
                'description' => 'The title of the appointment.',
                'example' => 'Vet Checkup',
            ],
            'scheduled_at' => [
                'description' => 'The scheduled date and time for the appointment in ISO 8601 format.',
                'example' => '2024-12-15T14:30:00Z',
            ],
            'notes' => [
                'description' => 'Additional notes about the appointment (optional).',
                'example' => 'Annual vaccination due',
            ],
        ];
    }
}
