@component('mail::message')
# You've Been Invited as a Pet Caregiver!

Hello,

{{ $inviterEmail }} has invited you to become a caregiver for their pet **{{ $petName }}**, a {{ $petSpecies }}.

## What This Means

As a caregiver, you'll be able to:
- View {{ $petName }}'s profile and information
- Log daily activities and special moments
- Complete routine care tasks
- Help keep track of {{ $petName }}'s well-being

## Accept Your Invitation

Click the button below to accept this invitation and start helping care for {{ $petName }}:

@component('mail::button', ['url' => $acceptUrl, 'color' => 'primary'])
Accept Invitation
@endcomponent

**Note**: This invitation will expire on {{ $expiresAt->format('F j, Y \a\t g:i A') }}. Please accept it before then to become a caregiver.

If you don't have a PetCare Companion account yet, you'll be prompted to create one using this email address.

If you didn't expect this invitation or don't wish to become a caregiver, you can safely ignore this email.

Thanks,
{{ config('app.name') }}

---
*This is an automated invitation from PetCare Companion. If you have questions about this invitation, please contact {{ $inviterEmail }}.*
@endcomponent
