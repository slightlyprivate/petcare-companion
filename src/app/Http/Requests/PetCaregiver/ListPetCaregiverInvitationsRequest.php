<?php

namespace App\Http\Requests\PetCaregiver;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request for listing caregiver invitations for the authenticated user.
 *
 * @group Pets
 */
class ListPetCaregiverInvitationsRequest extends FormRequest
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
}
