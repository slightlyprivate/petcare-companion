# Security

This document outlines the security measures and posture of the PetCare Companion application.

## Development Environment

- Docker Compose exposes services (e.g., MySQL on 127.0.0.1:3307, Redis on 127.0.0.1:6379) only on
  localhost to prevent external access.
- Laravel Horizon dashboard is protected by authentication and only accessible in
  development/testing environments.

## Production Readiness

- Sessions are encrypted at rest using Laravel's built-in encryption.
- Environment variables are not committed to version control; `.env.example` provides a template
  without sensitive values.
- File operations (e.g., user data exports) use Laravel's Storage facade with private disks,
  constraining paths to prevent arbitrary file access.
- Rate limiting is enforced on all API endpoints to mitigate abuse.
- CORS is explicitly configured with least-privilege origins.
- Debug mode is disabled by default and must be explicitly enabled.

## Future Features

New features (e.g., caregivers, activities, routines) will adhere to the same security standards:

- Input validation and sanitization.
- Authentication and authorization checks.
- Encrypted data storage.
- Rate limiting and CORS policies.

## Reporting Vulnerabilities

If you discover a security issue, please report it privately to the maintainers. Do not create
public issues for security concerns.
