@extends('emails.layouts.app')

@section('title', 'Pet Information Updated')

@section('content')
	<h1 style="margin:0 0 12px 0;font-size:24px;line-height:32px;color:#0f172a;">Pet Information Updated</h1>
	<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;color:#374151;">The details for {{ $petName }} have been updated.</p>

	<div style="margin:0 0 14px 0;padding:12px 14px;border:1px solid #e5e7eb;border-radius:10px;background-color:#f8fafc;">
		<p style="margin:0 0 6px 0;font-size:15px;line-height:22px;color:#374151;font-weight:600;">Changes made</p>
		<p style="margin:0;font-size:15px;line-height:22px;color:#111827;">{{ $changedFields }}</p>
	</div>

	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 16px 0;border-collapse:collapse;">
		<tr>
			<td style="padding:12px 14px;border:1px solid #e5e7eb;border-radius:10px;background-color:#ffffff;">
				<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;color:#111827;border-collapse:collapse;">
					<tr>
						<td style="padding:6px 0;font-weight:600;color:#374151;width:120px;">Name</td>
						<td style="padding:6px 0;color:#111827;">{{ $petName }}</td>
					</tr>
					<tr>
						<td style="padding:6px 0;font-weight:600;color:#374151;width:120px;">Species</td>
						<td style="padding:6px 0;color:#111827;">{{ $species }}</td>
					</tr>
					<tr>
						<td style="padding:6px 0;font-weight:600;color:#374151;width:120px;">Breed</td>
						<td style="padding:6px 0;color:#111827;">{{ $breed }}</td>
					</tr>
					<tr>
						<td style="padding:6px 0;font-weight:600;color:#374151;width:120px;">Status</td>
						<td style="padding:6px 0;color:#111827;">{{ $status }}</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<p style="margin:0;font-size:14px;line-height:22px;color:#6b7280;">If you did not make these changes, please contact our support team.</p>
@endsection
