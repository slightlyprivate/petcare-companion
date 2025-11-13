<?php

namespace App\Http\Requests\Appointment;

/**
 * Request class for showing an appointment.
 *
 * @group Appointments
 */
class AppointmentShowRequest extends \Illuminate\Foundation\Http\FormRequest
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
            'include' => ['sometimes', 'string', 'in:pet'],
        ];
    }

    /**
     * Get query parameters for API documentation.
     *
     * @return array<string, array<string, mixed>>
     */
    public function queryParameters(): array
    {
        return [
            'include' => [
                'description' => 'Include related data (pet).',
                'example' => 'pet',
            ],
        ];
    }
}
