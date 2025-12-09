@extends('emails.layouts.app')

@section('title', 'Account Deletion Initiated')

@section('content')
	<h1 style="margin:0 0 12px 0;font-size:24px;line-height:32px;color:#0f172a;">Account Deletion Initiated</h1>
	<p style="margin:0 0 14px 0;font-size:16px;line-height:24px;color:#374151;">We received your request to delete your account and associated data.</p>

	<div style="margin:0 0 14px 0;padding:12px 14px;border:1px solid #e5e7eb;border-radius:10px;background-color:#fef2f2;">
		<p style="margin:0 0 6px 0;font-size:15px;line-height:22px;color:#374151;font-weight:600;">What happens next</p>
		<p style="margin:0;font-size:15px;line-height:22px;color:#111827;">Your deletion request is being processed. This action is permanent and cannot be undone.</p>
	</div>

	<div style="margin:0 0 14px 0;padding:12px 14px;border:1px solid #e5e7eb;border-radius:10px;background-color:#f8fafc;">
		<p style="margin:0 0 6px 0;font-size:15px;line-height:22px;color:#374151;font-weight:600;">Data being deleted</p>
		<ul style="margin:8px 0 0 18px;padding:0;font-size:15px;line-height:22px;color:#374151;">
			<li>User account</li>
			<li>Pets and their records</li>
			<li>Appointments</li>
			<li>Gifts</li>
			<li>Notification preferences</li>
			<li>All personally identifiable information</li>
		</ul>
	</div>

	<p style="margin:0;font-size:14px;line-height:22px;color:#6b7280;">If you did not request this deletion or want to cancel, contact support immediately.</p>
@endsection
