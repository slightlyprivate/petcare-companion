<?php

namespace App\Http\Requests\Auth\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for updating notification preferences.
 */
class UpdateNotificationPreferenceRequest extends FormRequest
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
            'type' => [
                'required',
                'string',
                'in:otp,login,gift,pet_update,sms,email',
            ],
            'enabled' => [
                'required',
                'boolean',
            ],
        ];
    }

    /**
     * Get the body parameters for this request.
     */
    public function bodyParameters(): array
    {
        return [
            'type' => [
                'description' => 'Notification type (e.g., otp, login, gift, pet_update, sms, email)',
                'example' => 'email',
            ],
            'enabled' => [
                'description' => 'Whether the notification type is enabled',
                'example' => true,
            ],
        ];
    }
}
