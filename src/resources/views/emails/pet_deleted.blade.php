@extends('emails.layouts.app')

@section('title', 'Pet Removed')

@section('content')
	<h1 style="margin:0 0 12px 0;font-size:24px;line-height:32px;color:#0f172a;">Pet Removed</h1>
	<p style="margin:0 0 14px 0;font-size:16px;line-height:24px;color:#374151;">{{ $petName }} has been removed from your PetCare Companion household.</p>

	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 16px 0;border-collapse:collapse;">
		<tr>
			<td style="padding:12px 14px;border:1px solid #e5e7eb;border-radius:10px;background-color:#fef2f2;">
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
				</table>
			</td>
		</tr>
	</table>

	<p style="margin:0;font-size:14px;line-height:22px;color:#6b7280;">If this removal was unexpected, contact support to discuss restoration options.</p>
@endsection
