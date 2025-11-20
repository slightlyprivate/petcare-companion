<?php

namespace App\Services\Pet;

use App\Mail\PetCaregiverInvitationMail;
use App\Models\Pet;
use App\Models\PetCaregiverInvitation;
use App\Models\PetUser;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * Service for managing pet caregiver invitations.
 *
 * @group Services
 */
class PetCaregiverService
{
    /**
     * Send a caregiver invitation email.
     */
    public function sendInvitation(Pet $pet, User $inviter, string $inviteeEmail): PetCaregiverInvitation
    {
        $invitation = PetCaregiverInvitation::create([
            'pet_id' => $pet->getKey(),
            'inviter_id' => $inviter->getKey(),
            'invitee_email' => $inviteeEmail,
        ]);

        $acceptUrl = config('services.frontend_url', env('FRONTEND_URL')) . '/caregiver-invitations/accept?token=' . $invitation->token;

        Mail::to($invitation->invitee_email)->queue(
            new PetCaregiverInvitationMail(
                $invitation,
                $pet,
                $inviter,
                $acceptUrl
            )
        );

        activity()
            ->performedOn($invitation)
            ->causedBy($inviter)
            ->withProperties([
                'pet_id' => $pet->getKey(),
                'invitee_email' => $inviteeEmail,
            ])
            ->log('caregiver_invitation_sent');

        return $invitation->fresh(['pet', 'inviter']);
    }

    /**
     * Accept a caregiver invitation.
     */
    public function acceptInvitation(string $token, User $user): array
    {
        $invitation = PetCaregiverInvitation::where('token', $token)->first();

        if (! $invitation) {
            return ['status' => 404, 'body' => ['message' => __('caregiver_invitation.errors.not_found'), 'error' => 'not_found']];
        }

        if ($invitation->status !== 'pending') {
            return ['status' => 422, 'body' => ['message' => __('caregiver_invitation.errors.invalid_status', ['status' => $invitation->status]), 'error' => 'invalid_invitation_status']];
        }

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'expired']);

            return ['status' => 422, 'body' => ['message' => __('caregiver_invitation.errors.expired'), 'error' => 'invitation_expired']];
        }

        if ($invitation->invitee_email !== $user->email) {
            return ['status' => 403, 'body' => ['message' => __('caregiver_invitation.errors.email_mismatch'), 'error' => 'email_mismatch']];
        }

        $existingRelationship = PetUser::where('pet_id', $invitation->pet_id)
            ->where('user_id', $user->getKey())
            ->first();

        if ($existingRelationship) {
            return ['status' => 422, 'body' => ['message' => __('caregiver_invitation.errors.already_has_access', ['role' => $existingRelationship->role]), 'error' => 'already_has_access']];
        }

        $invitation->markAsAccepted($user->getKey());

        $petUser = PetUser::create([
            'pet_id' => $invitation->pet_id,
            'user_id' => $user->getKey(),
            'role' => 'caregiver',
        ]);

        activity()
            ->performedOn($invitation)
            ->causedBy($user)
            ->withProperties([
                'pet_id' => $invitation->pet_id,
                'invitee_email' => $invitation->invitee_email,
            ])
            ->log('caregiver_invitation_accepted');

        return [
            'status' => 200,
            'body' => [
                'message' => __('caregiver_invitation.accepted.success'),
                'data' => [
                    'pet_id' => $petUser->pet_id,
                    'role' => $petUser->role,
                    'created_at' => $petUser->created_at->toIso8601String(),
                ],
            ],
        ];
    }

    /**
     * List caregiver invitations for a user.
     */
    public function listForUser(User $user): array
    {
        $sent = PetCaregiverInvitation::where('inviter_id', $user->getKey())
            ->with(['pet:id,name,species', 'invitee:id,email'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($invitation) {
                return [
                    'id' => $invitation->id,
                    'type' => 'sent',
                    'pet' => [
                        'id' => $invitation->pet->id,
                        'name' => $invitation->pet->name,
                        'species' => $invitation->pet->species,
                    ],
                    'invitee_email' => $invitation->invitee_email,
                    'status' => $invitation->status,
                    'expires_at' => $invitation->expires_at->toIso8601String(),
                    'created_at' => $invitation->created_at->toIso8601String(),
                    'accepted_at' => $invitation->accepted_at?->toIso8601String(),
                ];
            });

        $received = PetCaregiverInvitation::where('invitee_email', $user->email)
            ->with(['pet:id,name,species', 'inviter:id,email'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($invitation) {
                return [
                    'id' => $invitation->id,
                    'type' => 'received',
                    'pet' => [
                        'id' => $invitation->pet->id,
                        'name' => $invitation->pet->name,
                        'species' => $invitation->pet->species,
                    ],
                    'inviter_email' => $invitation->inviter->email,
                    'status' => $invitation->status,
                    'expires_at' => $invitation->expires_at->toIso8601String(),
                    'token' => $invitation->status === 'pending' && ! $invitation->isExpired() ? $invitation->token : null,
                    'created_at' => $invitation->created_at->toIso8601String(),
                    'accepted_at' => $invitation->accepted_at?->toIso8601String(),
                ];
            });

        return ['sent' => $sent, 'received' => $received];
    }

    /**
     * Revoke a caregiver invitation.
     */
    public function revokeInvitation(PetCaregiverInvitation $invitation, User $actor): array
    {
        if ($invitation->status === 'accepted' && $invitation->invitee_id) {
            PetUser::where('pet_id', $invitation->pet_id)
                ->where('user_id', $invitation->invitee_id)
                ->where('role', 'caregiver')
                ->delete();
        }

        $invitation->markAsRevoked();

        activity()
            ->performedOn($invitation)
            ->causedBy($actor)
            ->withProperties([
                'pet_id' => $invitation->pet_id,
                'invitee_email' => $invitation->invitee_email,
                'previous_status' => $invitation->getOriginal('status'),
            ])
            ->log('caregiver_invitation_revoked');

        return ['status' => 200, 'body' => ['message' => __('caregiver_invitation.revoked.success')]];
    }

    /**
     * List all caregivers for a specific pet.
     */
    public function listCaregivers(Pet $pet): array
    {
        return $pet->users()
            ->withPivot('role', 'created_at')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'joined_at' => $user->pivot->created_at?->toIso8601String(),
                ];
            })
            ->toArray();
    }

    /**
     * Remove a caregiver from a pet.
     */
    public function removeCaregiver(Pet $pet, string $userId, User $actor): array
    {
        $petUser = $pet->petUsers()->where('user_id', $userId)->first();

        if (! $petUser) {
            return ['status' => 404, 'body' => ['message' => __('caregiver.errors.not_found'), 'error' => 'caregiver_not_found']];
        }

        if ($petUser->role === 'owner') {
            return ['status' => 403, 'body' => ['message' => __('caregiver.errors.cannot_remove_owner'), 'error' => 'cannot_remove_owner']];
        }

        $petUser->delete();

        activity()
            ->performedOn($pet)
            ->causedBy($actor)
            ->withProperties([
                'pet_id' => $pet->getKey(),
                'removed_user_id' => $userId,
                'role' => $petUser->role,
            ])
            ->log('caregiver_removed');

        return ['status' => 200, 'body' => ['message' => __('caregiver.removed.success')]];
    }
}
