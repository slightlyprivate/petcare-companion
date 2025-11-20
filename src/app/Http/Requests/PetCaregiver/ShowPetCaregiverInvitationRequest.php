<?php

namespace App\Http\Requests\PetCaregiver;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request for showing a caregiver invitation (currently unused placeholder).
 *
 * @group Pets
 */
class ShowPetCaregiverInvitationRequest extends FormRequest
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
