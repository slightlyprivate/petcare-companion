<?php

namespace App\Http\Requests\PetCaregiver;

use App\Models\PetCaregiverInvitation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request class for creating a new caregiver invitation.
 *
 * @group Pets
 */
class CreatePetCaregiverInvitationRequest extends FormRequest
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
        $petParam = $this->route('pet');
        $petId = $petParam instanceof \App\Models\Pet ? $petParam->id : $petParam;

        return [
            'invitee_email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::notIn([$this->user()?->email]),
                function ($attribute, $value, $fail) use ($petId) {
                    $exists = PetCaregiverInvitation::where('pet_id', $petId)
                        ->where('invitee_email', $value)
                        ->where('status', 'pending')
                        ->where('expires_at', '>', now())
                        ->exists();

                    if ($exists) {
                        $fail(__('caregiver_invitation.validation.invitee_email.duplicate'));
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'invitee_email.required' => __('caregiver_invitation.validation.invitee_email.required'),
            'invitee_email.email' => __('caregiver_invitation.validation.invitee_email.email'),
            'invitee_email.max' => __('caregiver_invitation.validation.invitee_email.max'),
            'invitee_email.not_in' => __('caregiver_invitation.validation.invitee_email.self_invite'),
            'invitee_email.unique' => __('caregiver_invitation.validation.invitee_email.duplicate'),
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
            'invitee_email' => [
                'description' => 'The email address of the person being invited as a caregiver.',
                'example' => 'friend@example.com',
            ],
        ];
    }
}
