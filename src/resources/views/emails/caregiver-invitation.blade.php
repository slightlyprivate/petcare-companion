@extends('emails.layouts.app')

@section('title', "You've Been Invited to Join a PetCare Companion Household")

@section('content')
	<h1 style="margin:0 0 12px 0;font-size:24px;line-height:32px;color:#0f172a;">You’ve Been Invited to Join a PetCare Companion Household</h1>
	<p style="margin:0 0 14px 0;font-size:16px;line-height:24px;color:#374151;">{{ $inviterName }} ({{ $inviterEmail }}) invited you to help care for {{ $petName }} ({{ $petSpecies }}).</p>

	<div style="margin:0 0 16px 0;padding:14px 16px;border:1px solid #e5e7eb;border-radius:10px;background-color:#f8fafc;">
		<p style="margin:0;font-size:15px;line-height:22px;color:#374151;font-weight:600;">As a caregiver you can:</p>
		<ul style="margin:10px 0 0 18px;padding:0;font-size:15px;line-height:22px;color:#374151;">
			<li>View {{ $petName }}’s profile and care info</li>
			<li>Log daily activities and special moments</li>
			<li>Complete routine care tasks</li>
			<li>Help keep track of {{ $petName }}’s well-being</li>
		</ul>
	</div>

	<p style="margin:0 0 14px 0;font-size:16px;line-height:24px;color:#374151;">Accept the invitation to join the household and start caring for {{ $petName }}.</p>

	<a href="{{ $acceptUrl }}" style="display:inline-block;margin:0 0 16px 0;padding:12px 18px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:10px;font-weight:700;font-size:15px;">Accept Invitation</a>

	<p style="margin:0 0 10px 0;font-size:14px;line-height:22px;color:#6b7280;">This invitation expires on {{ $expiresAt->format('F j, Y \a\t g:i A T') }}.</p>
	<p style="margin:0;font-size:14px;line-height:22px;color:#6b7280;">If you weren’t expecting this, you can ignore this email.</p>
@endsection
