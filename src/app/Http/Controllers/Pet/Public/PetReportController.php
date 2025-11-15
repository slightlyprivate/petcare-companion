<?php

namespace App\Http\Controllers\Pet\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Pet\Directory\PublicPetReportResource;
use App\Models\Pet;

/**
 * Controller for detailed public pet reports.
 *
 * Provides comprehensive reporting including gift summaries by type
 * and wallet transaction audit trails for transparency.
 *
 * @unauthenticated
 *
 * @group Pets
 */
class PetReportController extends Controller
{
    /**
     * Get detailed report for a public pet.
     *
     * Returns gift summaries by type and transaction audit trail showing
     * all wallet deductions for gifts sent to the pet.
     *
     * @param  string  $petId  The UUID of the pet
     */
    public function show(string $petId): PublicPetReportResource
    {
        $pet = Pet::query()
            ->where('is_public', true)
            ->whereId($petId)
            ->with([
                'gifts' => fn ($q) => $q->with('giftType')->orderBy('completed_at', 'desc'),
            ])
            ->firstOrFail();

        return new PublicPetReportResource($pet);
    }
}
