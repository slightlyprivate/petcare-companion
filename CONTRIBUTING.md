# Contributing to PetCare Companion

Thanks for your interest in improving PetCare Companion! This repository powers a Laravel API, a
React client, and supporting tooling. Contributions of any size are welcome.

## Getting Started Locally

1. Copy `.env.example` to `.env` and adjust any local overrides you need.
2. Start the stack: `docker-compose up --build`.
3. Install PHP dependencies: `docker-compose exec app composer install`.
4. Install JavaScript dependencies: `docker-compose exec app npm install` and `cd ui && npm install`
   as needed.
5. Run the database migrations and seeders: `docker-compose exec app php artisan migrate --seed`.

## Coding Standards

- PHP: follow PSR-12 and Laravel best practices. Use `docker-compose exec app ./vendor/bin/pint` for
  formatting and `docker-compose exec app ./vendor/bin/phpstan` for static analysis.
- JavaScript/TypeScript: run `npm run lint` in the root and `npm run lint` inside `ui/` to apply
  Prettier and ESLint rules.
- Favor small, focused pull requests with clear commit messages.

## Issues

- Search existing issues before filing a new one.
- For bugs, include reproduction steps, expected results, and actual results. Attach logs or
  screenshots when helpful.
- For feature requests, share the problem you are solving and any acceptance criteria.

## Pull Requests

- Create a feature branch from `main` using the `feature/*` or `fix/*` naming convention.
- Ensure automated tests pass: `docker-compose exec app php artisan test`.
- Update documentation (`README.md`, `docs/*`) when behavior or configuration changes.
- Request a review when your changes are ready. PR descriptions should summarize the change, include
  motivation, and note any follow-up work.

## Commit Messages

Commit messages should be descriptive and written in the imperative mood (e.g., "Add caregiver
scheduling endpoint"). Use multiple commits when it clarifies the review process; squash merges keep
history linear once reviewed.
