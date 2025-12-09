@extends('emails.layouts.app')

@section('title', 'Your Account Has Been Deleted')

@section('content')
	<h1 style="margin:0 0 12px 0;font-size:24px;line-height:32px;color:#0f172a;">Your PetCare Companion Account Has Been Deleted</h1>
	<p style="margin:0 0 14px 0;font-size:16px;line-height:24px;color:#374151;">Your request to delete your account and associated data has been completed.</p>

	<div style="margin:0 0 14px 0;padding:12px 14px;border:1px solid #e5e7eb;border-radius:10px;background-color:#f8fafc;">
		<p style="margin:0 0 6px 0;font-size:15px;line-height:22px;color:#374151;font-weight:600;">What we deleted</p>
		<ul style="margin:8px 0 0 18px;padding:0;font-size:15px;line-height:22px;color:#374151;">
			<li>Your user account</li>
			<li>Your pets and their records</li>
			<li>Your appointments</li>
			<li>Your gifts</li>
			<li>Your notification preferences</li>
			<li>All personally identifiable information</li>
		</ul>
	</div>

	<p style="margin:0 0 6px 0;font-size:14px;line-height:22px;color:#6b7280;">We permanently removed this data from our systems. This action cannot be undone.</p>
	<p style="margin:0;font-size:14px;line-height:22px;color:#6b7280;">If you have questions about this deletion, please contact our support team.</p>
@endsection
