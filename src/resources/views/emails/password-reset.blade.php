@extends('emails.layouts.app')

@section('title', 'Reset Your Password')

@section('content')
    <h1 style="margin:0 0 12px 0;font-size:24px;line-height:32px;color:#0f172a;">Reset Your Password</h1>
    <p style="margin:0 0 14px 0;font-size:16px;line-height:24px;color:#374151;">We received a request to reset your password. Use the button below to choose a new password.</p>

    <a href="{{ $resetUrl }}" style="display:inline-block;margin:0 0 16px 0;padding:12px 18px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:10px;font-weight:700;font-size:15px;">Reset Password</a>

    <p style="margin:0 0 8px 0;font-size:14px;line-height:22px;color:#6b7280;">This link expires in {{ $expiresIn }} minutes.</p>
    <p style="margin:0;font-size:14px;line-height:22px;color:#6b7280;">If you didn’t request a password reset, you can ignore this email.</p>
@endsection
