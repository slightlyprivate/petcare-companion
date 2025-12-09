@extends('emails.layouts.app')

@section('title', 'Your Data Export is Ready')

@section('content')
	<h1 style="margin:0 0 12px 0;font-size:24px;line-height:32px;color:#0f172a;">Your Data Export is Ready</h1>
	<p style="margin:0 0 14px 0;font-size:16px;line-height:24px;color:#374151;">Your personal data export is ready for download. It includes your account information, pets, gifts, and appointments.</p>

	<div style="margin:0 0 14px 0;padding:12px 14px;border:1px solid #e5e7eb;border-radius:10px;background-color:#f8fafc;">
		<p style="margin:0 0 6px 0;font-size:15px;line-height:22px;color:#374151;font-weight:600;">What's included</p>
		<ul style="margin:8px 0 0 18px;padding:0;font-size:15px;line-height:22px;color:#374151;">
			<li>User profile and account information</li>
			<li>All pets you’ve registered</li>
			<li>Gifts you’ve sent</li>
			<li>Appointments associated with your pets</li>
		</ul>
	</div>

	<a href="{{ $downloadUrl }}" style="display:inline-block;margin:0 0 16px 0;padding:12px 18px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:10px;font-weight:700;font-size:15px;">Download Your Data</a>

	<p style="margin:0 0 8px 0;font-size:14px;line-height:22px;color:#6b7280;">This link is valid for 48 hours.</p>
	<p style="margin:0;font-size:14px;line-height:22px;color:#6b7280;">If you did not request this export, you can ignore this email.</p>
@endsection
