<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // No authentication required per PRD
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pet_id' => ['required', 'integer', 'exists:pets,id'],
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
            'pet_id.required' => 'Pet selection is required.',
            'pet_id.exists' => 'Selected pet does not exist.',
            'title.required' => 'Appointment title is required.',
            'scheduled_at.required' => 'Appointment date and time is required.',
            'scheduled_at.after' => 'Appointment must be scheduled for a future date and time.',
        ];
    }
}