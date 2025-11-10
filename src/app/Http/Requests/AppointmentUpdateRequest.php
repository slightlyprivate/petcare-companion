<?php

namespace App\Http\Requests;

/**
 * Request class for updating an appointment.
 *
 * @group Appointments
 */
class AppointmentUpdateRequest extends \Illuminate\Foundation\Http\FormRequest
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
}
