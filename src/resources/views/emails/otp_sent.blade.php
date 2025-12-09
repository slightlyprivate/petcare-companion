@extends('emails.layouts.app')

@section('title', 'Your Authentication Code')

@section('content')
	<h1 style="margin:0 0 12px 0;font-size:24px;line-height:32px;color:#0f172a;">Your Authentication Code</h1>
	<p style="margin:0 0 16px 0;font-size:16px;line-height:24px;color:#374151;">Here is your one-time login code.</p>

	<div style="margin:12px 0 18px 0;padding:14px 18px;border-radius:10px;background-color:#f8fafc;border:1px dashed #cbd5e1;text-align:center;">
		<span style="font-size:28px;letter-spacing:4px;font-weight:700;color:#0f172a;">{{ $code }}</span>
	</div>

	<p style="margin:0 0 12px 0;font-size:16px;line-height:24px;color:#374151;">This code expires in <strong>5 minutes</strong>. For your security, do not share it with anyone.</p>
	<p style="margin:0;font-size:14px;line-height:22px;color:#6b7280;">If you did not request this code, you can safely ignore this email.</p>
@endsection
