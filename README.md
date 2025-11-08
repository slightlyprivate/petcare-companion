# PetCare Companion

Laravel 11 micro-app demonstrating disciplined MVC, RESTful API design, and Dockerized PHP 8.3/MySQL 8 delivery for a pet scheduling domain.

## Stack Signals

- **Backend:** PHP 8.3, Laravel 11, MySQL 8, Nginx, Docker Compose.
- **Patterns:** Request validation, Eloquent relationships (Pet ⇢ Appointments), Resource transformers, JSON:API-style responses.
- **Craft:** .env hygiene, migrations + seeders, Git conventional commits, container-first workflow.

## Repo Snapshot

```bash
petcare-companion/
├─ docker/ (PHP-FPM + Nginx)
├─ docker-compose.yml
├─ .env.example
├─ docs/architecture.md
├─ docs/api.postman_collection.json
├─ docs/screenshots/
└─ src/  # Laravel app
```

## Quick Start

```bash
cp .env.example .env
docker compose up -d db
docker compose run --rm app bash -lc "composer install && php artisan key:generate && php artisan migrate --seed"
docker compose up -d
open http://localhost:8080
```

## API Summary

| Method | Path                              | Description                                   |
|--------|-----------------------------------|-----------------------------------------------|
| GET    | /api/pets                         | Paginated pets list                            |
| POST   | /api/pets                         | Create pet (validated)                        |
| GET    | /api/pets/{id}                    | Show pet, supports `?include=appointments`    |
| PUT    | /api/pets/{id}                    | Update pet                                    |
| DELETE | /api/pets/{id}                    | Delete pet (204)                              |
| GET    | /api/pets/{id}/appointments       | List a pet’s appointments                     |
| POST   | /api/appointments                 | Create appointment for a pet                  |
| PUT    | /api/appointments/{id}            | Update appointment                            |
| DELETE | /api/appointments/{id}            | Delete appointment (204)                      |

> Sample requests: `docs/api.postman_collection.json`.

## Tests

```bash
docker compose run --rm app bash -lc "./vendor/bin/phpunit --testdox"
```

Feature coverage proves listing/creating pets and appointments with seeded fixtures.

## Deliverables Checklist

- Seeded database with pets + upcoming appointments.
- REST endpoints + Postman export.
- Feature tests + JSON API resources.
- Nginx/PHP-FPM Docker stack with MySQL volume.
- Screenshots under `docs/screenshots/`.
- Architecture note: `docs/architecture.md` (domain model, layering, validation, error handling).
- README kept minimal and production-ready.

## Why It Matters

Built to showcase modern Laravel proficiency—clean architecture, container fluency, and pragmatic APIs—for PHP/MySQL roles emphasizing MVC discipline.
