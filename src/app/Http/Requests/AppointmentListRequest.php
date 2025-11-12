<?php

namespace App\Http\Requests;

/**
 * Request class for listing appointments.
 *
 * @group Appointments
 */
class AppointmentListRequest extends \Illuminate\Foundation\Http\FormRequest
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
            'pet_id' => ['sometimes', 'integer', 'exists:pets,id'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'sort_by' => ['sometimes', 'string', 'in:scheduled_at,title'],
            'sort_direction' => ['sometimes', 'string', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
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
            'pet_id' => [
                'description' => 'Filter appointments by pet ID.',
                'example' => 1,
            ],
            'date_from' => [
                'description' => 'Filter appointments from this date (YYYY-MM-DD format).',
                'example' => '2024-01-01',
            ],
            'date_to' => [
                'description' => 'Filter appointments up to this date (YYYY-MM-DD format).',
                'example' => '2024-12-31',
            ],
            'sort_by' => [
                'description' => 'Field to sort by.',
                'example' => 'scheduled_at',
            ],
            'sort_direction' => [
                'description' => 'Sort direction (asc or desc).',
                'example' => 'asc',
            ],
            'per_page' => [
                'description' => 'Number of items per page (1-50).',
                'example' => 15,
            ],
        ];
    }
}
