<?php

namespace App\Http\Controllers\Pet;

use App\Http\Controllers\Controller;
use App\Http\Requests\PetCaregiver\AcceptPetCaregiverInvitationRequest;
use App\Http\Requests\PetCaregiver\CreatePetCaregiverInvitationRequest;
use App\Http\Requests\PetCaregiver\ListPetCaregiverInvitationsRequest;
use App\Http\Requests\PetCaregiver\RevokePetCaregiverInvitationRequest;
use App\Http\Requests\PetCaregiver\ShowPetCaregiverInvitationRequest;
use App\Http\Requests\PetCaregiver\UpdatePetCaregiverInvitationRequest;
use App\Models\Pet;
use App\Models\PetCaregiverInvitation;
use App\Services\Pet\PetCaregiverService;
use Illuminate\Http\JsonResponse;

/**
 * Controller for managing pet caregiver invitations.
 *
 * @authenticated
 *
 * @group Pets
 */
class PetCaregiverInvitationController extends Controller
{
    public function __construct(private PetCaregiverService $caregiverService) {}

    /**
     * Send a caregiver invitation for a pet.
     *
     * Invites a user to become a caregiver for the specified pet. Only pet owners can send invitations.
     *
     * @urlParam pet string required The UUID of the pet. Example: 9d4e8c5a-1234-5678-9abc-def012345678
     */
    public function store(CreatePetCaregiverInvitationRequest $request, Pet $pet): JsonResponse
    {
        $this->authorize('inviteCaregiver', $pet);

        $data = $request->validated();

        $invitation = $this->caregiverService->sendInvitation($pet, $request->user(), $data['invitee_email']);

        return response()->json([
            'message' => __('caregiver_invitation.sent.success'),
            'data' => [
                'id' => $invitation->getKey(),
                'pet_id' => $invitation->pet_id,
                'invitee_email' => $invitation->invitee_email,
                'status' => $invitation->status,
                'expires_at' => $invitation->expires_at->toIso8601String(),
                'created_at' => $invitation->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Accept a caregiver invitation.
     *
     * Accepts an invitation and creates a caregiver relationship for the authenticated user.
     *
     * @urlParam token string required The invitation token. Example: abc123def456...
     */
    public function accept(AcceptPetCaregiverInvitationRequest $request, string $token): JsonResponse
    {
        $user = $request->user();

        $result = $this->caregiverService->acceptInvitation($token, $user);

        return response()->json($result['body'], $result['status']);
    }

    /**
     * List caregiver invitations for the authenticated user.
     *
     * Returns both sent and received invitations with their current status.
     */
    public function index(ListPetCaregiverInvitationsRequest $request): JsonResponse
    {
        $user = $request->user();

        $lists = $this->caregiverService->listForUser($user);

        return response()->json(['data' => $lists], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowPetCaregiverInvitationRequest $request, string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePetCaregiverInvitationRequest $request, string $id)
    {
        //
    }

    /**
     * Revoke a caregiver invitation.
     *
     * Only the inviter can revoke an invitation. If accepted, also removes the caregiver relationship.
     *
     * @urlParam id integer required The invitation ID. Example: 123
     */
    public function destroy(RevokePetCaregiverInvitationRequest $request, PetCaregiverInvitation $invitation): JsonResponse
    {
        $this->authorize('delete', $invitation);

        $result = $this->caregiverService->revokeInvitation($invitation, $request->user());

        return response()->json($result['body'], $result['status']);
    }
}
