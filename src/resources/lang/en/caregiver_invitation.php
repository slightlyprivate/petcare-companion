<?php

return [
    'sent' => [
        'success' => 'Caregiver invitation sent successfully.',
    ],

    'accepted' => [
        'success' => 'Invitation accepted successfully. You are now a caregiver for this pet.',
    ],

    'revoked' => [
        'success' => 'Invitation revoked successfully.',
    ],

    'errors' => [
        'not_found' => 'Invitation not found.',
        'invalid_status' => 'This invitation has already been :status.',
        'expired' => 'This invitation has expired.',
        'email_mismatch' => 'This invitation was sent to a different email address.',
        'already_has_access' => 'You already have access to this pet as a :role.',
    ],

    'validation' => [
        'invitee_email' => [
            'required' => 'Email address is required.',
            'email' => 'Please provide a valid email address.',
            'max' => 'Email address must not exceed 255 characters.',
            'self_invite' => 'You cannot invite yourself as a caregiver.',
            'duplicate' => 'A pending invitation has already been sent to this email address.',
        ],
    ],
];
