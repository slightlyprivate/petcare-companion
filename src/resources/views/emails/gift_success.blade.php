@extends('emails.layouts.app')

@section('title', 'Thank You for Your Gift')

@section('content')
	<h1 style="margin:0 0 12px 0;font-size:24px;line-height:32px;color:#0f172a;">Thank You for Your Gift!</h1>
	<p style="margin:0 0 14px 0;font-size:16px;line-height:24px;color:#374151;">We appreciate your contribution to {{ $petName }}’s care.</p>

	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 16px 0;border-collapse:collapse;">
		<tr>
			<td style="padding:12px 14px;border:1px solid #e5e7eb;border-radius:10px;background-color:#f8fafc;">
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;color:#111827;border-collapse:collapse;">
					<tr>
						<td style="padding:6px 0;font-weight:600;color:#374151;width:140px;">Pet</td>
						<td style="padding:6px 0;color:#111827;">{{ $petName }}</td>
					</tr>
					<tr>
						<td style="padding:6px 0;font-weight:600;color:#374151;width:140px;">Credits Used</td>
						<td style="padding:6px 0;color:#111827;">{{ $credits }}</td>
					</tr>
					<tr>
						<td style="padding:6px 0;font-weight:600;color:#374151;width:140px;">Processed On</td>
						<td style="padding:6px 0;color:#111827;">{{ $date }}</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<p style="margin:0;font-size:14px;line-height:22px;color:#6b7280;">Your support makes a real difference. Thank you for helping our pets thrive.</p>
@endsection
