@component('mail::message')
# Your Data Export is Ready

Dear {{ $user->email }},

Your personal data export from PetCare Companion is now ready. This export contains all your user information, pets, gifts, and appointments in a secure ZIP file format.

## What's Included

- **User Profile**: Your account information
- **Pets**: All pets you've registered
- **Gifts**: All gifts you've sent
- **Appointments**: All appointments associated with your pets

The export contains your data in JSON format for easy access and portability.

@component('mail::button', ['url' => $downloadUrl, 'color' => 'primary'])
Download Your Data
@endcomponent

**Note**: This download link is valid for 7 days. For security reasons, please download your data promptly.

If you did not request this data export, please ignore this email.

Thanks,
{{ config('app.name') }}

---
*This email was generated in response to a data export request. For questions about your data and privacy, please visit our privacy policy.*
@endcomponent
