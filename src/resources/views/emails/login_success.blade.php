@extends('emails.layouts.app')

@section('title', 'Successful Login to PetCare Companion')

@section('content')
	<h1 style="margin:0 0 12px 0;font-size:24px;line-height:32px;color:#0f172a;">Successful Login to PetCare Companion</h1>
	<p style="margin:0 0 18px 0;font-size:16px;line-height:24px;color:#374151;">You just signed in to your account. Review the details below.</p>

	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 12px 0;border-collapse:collapse;">
		<tr>
			<td style="padding:10px 12px;border:1px solid #e5e7eb;border-radius:8px;background-color:#f8fafc;">
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:15px;color:#111827;">
					<tr>
						<td style="padding:6px 0;font-weight:600;width:120px;color:#374151;">Time</td>
						<td style="padding:6px 0;color:#111827;">{{ $time }}</td>
					</tr>
					@if(! empty($ipAddress))
						<tr>
							<td style="padding:6px 0;font-weight:600;width:120px;color:#374151;">IP Address</td>
							<td style="padding:6px 0;color:#111827;">{{ $ipAddress }}</td>
						</tr>
					@endif
					@if(! empty($device))
						<tr>
							<td style="padding:6px 0;font-weight:600;width:120px;color:#374151;">Device</td>
							<td style="padding:6px 0;color:#111827;">{{ $device }}</td>
						</tr>
					@endif
				</table>
			</td>
		</tr>
	</table>

	<p style="margin:0;font-size:14px;line-height:22px;color:#6b7280;">If this wasn't you, reset your password and contact our support team right away.</p>
@endsection
