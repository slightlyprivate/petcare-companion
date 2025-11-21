<?php

namespace App\Http\Requests\PetCaregiver;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request for revoking (deleting) a caregiver invitation.
 *
 * @group Pets
 */
class RevokePetCaregiverInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Revoking an invitation does not require a request body.
     *
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [];
    }
}
