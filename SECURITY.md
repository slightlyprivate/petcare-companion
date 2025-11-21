# Security Policy

This document describes how to report vulnerabilities and what to expect from the maintainers of
PetCare Companion.

## Supported Versions

- `main` — actively maintained and supported.
- All other branches — unsupported; use at your own risk.

## Reporting a Vulnerability

Please email `security@slightlyprivate.com` with detailed steps to reproduce the issue. Include any
logs, console output, or proof-of-concept code needed to help us understand the impact. Do not open
public issues for security concerns.

We acknowledge receipt within three business days. Once triaged, we provide periodic updates until
the report is resolved. Credit is granted to researchers who request it in their disclosure email.

## Security Audit Process

- Automated checks: dependency scans, static analysis, and container image vulnerability scans run
  as part of the CI pipeline.
- Manual review: significant changes undergo peer review focusing on authentication, authorization,
  and data handling.
- Audit agents: our automated audit workflow performs regular verification of configuration and
  dependency health; escalations are tracked in GitHub issues.

## Disclosure Policy

We follow a 90-day disclosure window. We coordinate a release containing the fix before public
disclosure and provide guidance for any mitigation steps users must take. If a vulnerability is not
resolved within the window, coordinated disclosure will be discussed with the reporter.

## Scope

- Laravel API backend
- React client application (directly communicates with Laravel via Nginx)
- Docker deployment assets and configuration
