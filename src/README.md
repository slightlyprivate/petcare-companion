# 🐾 PetCare Companion — Laravel API

A modern Laravel 12 REST API for pet and appointment management, demonstrating MVC architecture,
Docker containerization, and comprehensive API design best practices.

## 🎯 Purpose & Role Alignment

Technology Stack: PHP 8.3 • Laravel 12 • MySQL 8.0 • Docker • PHPUnit Architecture: RESTful API
following MVC pattern with resource-based endpoints Role: Educational demonstration of modern
Laravel development practices

This project showcases:

- REST API Design: Resource controllers, API resources, pagination
- Laravel Best Practices: Form requests, Eloquent models, factories
- Payment Integration: Stripe Checkout for credit bundle purchases
- Audit Logging: Comprehensive activity tracking with Spatie Activity Log
- Notification System: Multi-channel notifications (Email + SMS) with user preferences
- Gift Economy: Virtual credits and symbolic gifting system
- Wallet Management: User credit balances and transaction history
- Docker Integration: Multi-container setup with app, database, and web services
- Comprehensive Testing: 100+ tests
- Modern PHP: PSR-12 standards, typed properties, dependency injection

## 🚀 Quick Start

Prerequisites

- Docker & Docker Compose
- Git

Setup (3 minutes)

```bash
# 1) Copy environment configuration (from repo root)
cp .env.example .env

# 2) Start containers (from repo root)
docker-compose up -d

# 3) Run migrations and seeders
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed

# 4) Configure Stripe environment (for payment features)
echo "STRIPE_KEY=pk_test_your_key_here" >> .env
echo "STRIPE_SECRET=sk_test_your_secret_here" >> .env
echo "STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret" >> .env

# 5) Run tests to verify
docker-compose exec app php artisan test
```

## 📧 Mail Configuration (Development)

The application uses OTP-based authentication. Emails are configured to use Laravel's log driver for
development:

```bash
docker-compose exec app tail -n 20 -f storage/logs/laravel.log
```

Mail Configuration Details

- Driver: Log (writes to `storage/logs/laravel.log`) by default
- Override `MAIL_MAILER` per environment (`smtp` for Mailhog, `ses` for staging, `postmark` in
  production)
- From Address: `noreply@petcare.local`
- Set `POSTMARK_TOKEN` in `.env` when using the Postmark transport
- Local debug endpoint `/postmark-test` (enabled when `APP_DEBUG=true` or `APP_ENV=local`) triggers
  a one-off test email using the currently active mailer

## 💳 Payment & Gift Economy

Stripe Checkout is used for virtual credit purchases. Use test keys in `.env`.

Gift economy features include virtual credits, server-enforced pricing, wallet management,
transaction history, webhooks, and status tracking.

Credit conversion is centralized in `App\\Constants\\CreditConstants`.

## 🔒 Audit Logging

Comprehensive audit logging (Spatie Activity Log) tracks sensitive and user events: user changes,
pet CRUD, appointments, gift status changes.

## Notifications (Email & SMS)

Multi-channel notifications using Laravel notifications + Twilio.

Notification preference endpoints include:

| Method | Endpoint                                         | Description          |
| ------ | ------------------------------------------------ | -------------------- |
| GET    | `/api/user/notification-preferences`             | Get user preferences |
| PUT    | `/api/user/notification-preferences`             | Update preference    |
| POST   | `/api/user/notification-preferences/disable-all` | Disable all          |
| POST   | `/api/user/notification-preferences/enable-all`  | Enable all           |

## Authentication Endpoints

Laravel Sanctum session-based authentication (SPA mode) is used for frontend integration.

| Method | Endpoint            | Description                   |
| ------ | ------------------- | ----------------------------- |
| POST   | `/api/auth/request` | Request OTP                   |
| POST   | `/api/auth/verify`  | Verify OTP and create session |
| GET    | `/api/auth/me`      | Get authenticated user info   |
| GET    | `/api/auth/status`  | Check auth status (204/401)   |
| POST   | `/api/auth/logout`  | Logout and invalidate session |

CSRF protection is required for all unsafe methods (POST, PUT, PATCH, DELETE). The UI fetches a CSRF
token from `/sanctum/csrf-cookie` before making authenticated requests.

## Example Entities

- Pets CRUD with appointments include
- Gifts with receipts export
- Credits purchase session and history

## 🧪 Testing & Quality

```bash
docker-compose exec app php artisan test
docker-compose exec app ./vendor/bin/pint
docker-compose exec app ./vendor/bin/phpstan analyse
```

## 📁 API Structure

```sh
src/
├── app/
│   ├── Http/Controllers/     # API controllers
│   ├── Http/Requests/        # Form validation
│   ├── Http/Resources/       # API transformations
│   ├── Models/               # Eloquent models
│   └── Services/             # Business logic services
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── tests/
└── routes/
    └── api.php
```

## 🤝 Contributing

1.  Create feature branch: `git checkout -b feature/amazing-feature`
2.  Run tests: `docker-compose exec app php artisan test`
3.  Open Pull Request

## 📄 License

MIT
