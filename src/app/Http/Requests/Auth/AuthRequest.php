<?php

namespace App\Http\Requests\Auth;

/**
 * Request class for authentication requests.
 *
 * @group Authentication
 */
class AuthRequest extends \Illuminate\Foundation\Http\FormRequest
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
            'email' => 'required|email',
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
            'email' => [
                'description' => 'The email address to send the OTP code to.',
                'example' => 'user@example.com',
            ],
        ];
    }
}
