<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PetCare Companion')</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f7fa;font-family:'Segoe UI', Tahoma, sans-serif;color:#111827;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fa;padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 18px rgba(17,24,39,0.07);">
                <tr>
                    <td style="padding:22px 32px;border-bottom:1px solid #e5e7eb;background-color:#ffffff;">
                        <div style="font-size:18px;font-weight:700;color:#0f172a;">PetCare Companion</div>
                        <div style="margin-top:4px;font-size:13px;color:#6b7280;">Caring for pets, one step at a time.</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px 32px 16px 32px;">
                        @yield('content')
                    </td>
                </tr>
                <tr>
                    <td style="padding:20px 32px 28px 32px;border-top:1px solid #e5e7eb;text-align:center;font-size:12px;line-height:18px;color:#6b7280;">
                        <div>PetCare Companion</div>
                        <div>Caring for pets, one step at a time.</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
