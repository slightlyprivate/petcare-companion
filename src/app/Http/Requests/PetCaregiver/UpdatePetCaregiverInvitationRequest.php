<?php

namespace App\Http\Requests\PetCaregiver;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request for updating a caregiver invitation (currently unused placeholder).
 *
 * @group Pets
 */
class UpdatePetCaregiverInvitationRequest extends FormRequest
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
