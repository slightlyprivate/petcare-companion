# 🐾 PetCare Companion

A modern **Laravel 12 REST API** for pet and appointment management, demonstrating **MVC architecture**, **Docker containerization**, and **comprehensive API design** best practices.

## 🎯 Purpose & Role Alignment

**Technology Stack**: PHP 8.3 • Laravel 12 • MySQL 8.0 • Docker • PHPUnit  
**Architecture**: RESTful API following MVC pattern with resource-based endpoints  
**Role**: Educational demonstration of modern Laravel development practices  

This project showcases:

- ✅ **REST API Design** - Resource controllers, API resources, pagination
- ✅ **Laravel Best Practices** - Form requests, Eloquent models, factories
- ✅ **Payment Integration** - Stripe Checkout for credit bundle purchases
- ✅ **Audit Logging** - Comprehensive activity tracking with Spatie Activity Log
- ✅ **Notification System** - Multi-channel notifications (Email + SMS) with user preferences
- ✅ **Gift Economy** - Virtual credits and symbolic gifting system
- ✅ **Wallet Management** - User credit balances and transaction history
- ✅ **Docker Integration** - Multi-container setup with app, database, and web services
- ✅ **Comprehensive Testing** - 109+ tests with 627+ assertions
- ✅ **Modern PHP** - PSR-12 standards, typed properties, dependency injection

## 🚀 Quick Start

### Prerequisites

- Docker & Docker Compose
- Git

### Setup (3 minutes)

```bash
# 1. Clone repository
git clone <repository-url>
cd petcare-companion

# 2. Copy environment configuration
cp .env.example .env

# 3. Start containers
docker-compose up -d

# 4. Run migrations and seeders
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed

# 5. Configure Stripe environment (for payment features)
echo "STRIPE_KEY=pk_test_your_key_here" >> .env
echo "STRIPE_SECRET=sk_test_your_secret_here" >> .env
echo "STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret" >> .env

# 6. Run tests to verify
docker-compose exec app php artisan test

```

**That's it!** 🎉 You now have a fully functional API with demo data.

### 📧 Mail Configuration (Development)

The application uses **OTP-based authentication**. Emails are configured to use Laravel's log driver for development:

```bash
# View OTP codes in logs
docker-compose exec app tail -f storage/logs/laravel.log
```

**Mail Configuration Details:**

- **Driver**: Log (writes to `storage/logs/laravel.log`)
- **From Address**: `noreply@petcare.local`
- **No external mail server required** for development

## 💳 Payment & Gift Economy Configuration

The application includes **Stripe Checkout integration** for virtual credit purchases in the gift economy system. For development:

```bash
# Use Stripe test keys in .env
STRIPE_KEY=pk_test_your_publishable_key
STRIPE_SECRET=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

**Gift Economy Features:**

- **Virtual Credits**: Users purchase credits via Stripe (not real currency)
- **Gift System**: Users send symbolic gifts to pets using wallet credits (server enforces catalog price)
- **Wallet Management**: Each user has a wallet tracking credit balance
- **Transaction History**: Full logging of all credit purchases and gifts
- **Webhook Processing**: Handles payment completion, expired sessions, and credit allocation
- **Status Tracking**: Real-time payment and gift status updates

Credit conversion is centralized in `App\Constants\CreditConstants`:

- Standard: 5 credits = $1.00 (1 credit = $0.20 = 20 cents)
- Helpers: `toCents(int $credits)`, `toDollars(int $credits)`, `fromCents(int $cents)`, `fromDollars(float $dollars)`

## 🔒 Audit Logging (Activity Tracking)

The application includes **comprehensive audit logging** via Spatie Activity Log to track all sensitive and user-triggered events.

**Tracked Events:**

- **User**: Email and role changes
- **Pets**: Create, update, delete operations with full history
- **Appointments**: Create, update, delete operations
- **Gifts**: Create and status transitions (pending → paid/failed)

**Features:**

- Automatic user attribution (who made the change)
- Old and new values captured for all updates
- Persistent storage in `activity_log` table
- Query-friendly indexed storage for audit reports
- Configuration in `config/activitylog.php`

**Example Query:**

```php
// Get all activities for a specific pet
$activities = \Spatie\Activitylog\Models\Activity::forSubject($pet)->get();

// Get who changed a pet
foreach ($activities as $activity) {
    echo "User {$activity->causer->email} {$activity->event}d pet at {$activity->created_at}";
}
```

### Notification System (Email & SMS)

The application includes a **comprehensive multi-channel notification system** using Laravel's notification framework with Twilio SMS integration.

**Notification Types:**

- **OTP Sent**: Sends authentication code via email and SMS
- **Login Success**: Confirms successful authentication
- **Gift Success**: Confirms gift completion with receipt details
- **Pet Updated**: Notifies about pet information changes

**Channels:**

- **Email**: Rich HTML emails with markdown templates and branding
- **SMS**: Twilio integration for text message delivery
- **Database**: Persistent notification storage for in-app notifications

**Features:**

- Automatic notification creation and delivery
- User notification preferences (opt-in/opt-out per type)
- Channel-level preferences (enable/disable email or SMS)
- Markdown email templates in `resources/views/emails/`
- Customizable message formatting per channel

**Notification Preference Endpoints:**

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/user/notification-preferences` | Get user preferences |
| `PUT` | `/api/user/notification-preferences` | Update preference type |
| `POST` | `/api/user/notification-preferences/disable-all` | Disable all notifications |
| `POST` | `/api/user/notification-preferences/enable-all` | Enable all notifications |

**Configuration:**

```bash
# .env
MAIL_DRIVER=log
TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_PHONE_NUMBER=+1234567890
```

**Example Usage:**

```php
// Send notification to user
Notification::send($user, new OtpSentNotification($code, $email));

// Check user preferences
$preferences = $user->notificationPreference;
if ($preferences->isNotificationEnabled('otp')) {
    // Send OTP notification
}
```

### Authentication Endpoints

| Method | Endpoint | Description | Features |
|--------|----------|-------------|----------|
| `POST` | `/api/auth/request` | Request OTP for authentication | Email validation |
| `POST` | `/api/auth/verify` | Verify OTP and get token | JWT token response |
| `GET` | `/api/auth/me` | Get authenticated user info | Token validation |

### Pet Management Endpoints

| Method | Endpoint | Description | Features |
|--------|----------|-------------|----------|
| `GET` | `/api/pets` | List all pets | Pagination, filtering, sorting |
| `POST` | `/api/pets` | Create new pet | Validation, error handling |
| `GET` | `/api/pets/{id}` | Show single pet | Include appointments |
| `PUT` | `/api/pets/{id}` | Update pet | Full validation |
| `DELETE` | `/api/pets/{id}` | Delete pet | Soft delete support |

### Appointment Management Endpoints

| Method | Endpoint | Description | Features |
|--------|----------|-------------|----------|
| `GET` | `/api/pets/{id}/appointments` | List pet's appointments | Advanced filtering |
| `POST` | `/api/pets/{id}/appointments` | Create appointment | Pet association |
| `GET` | `/api/appointments/{id}` | Show appointment | Include pet data |
| `PUT` | `/api/appointments/{id}` | Update appointment | Status management |
| `DELETE` | `/api/appointments/{id}` | Delete appointment | Cascade handling |

### Payment & Gift Endpoints

| Method | Endpoint | Description | Features |
|--------|----------|-------------|----------|
| `POST` | `/api/pets/{id}/gifts` | Send gift to pet | Wallet debit, server-side pricing, validation |
| `GET` | `/api/gifts/{id}/receipt` | Get gift/credit receipt | PDF export support |
| `POST` | `/api/webhooks/stripe` | Stripe webhook handler | Payment status updates |

### Credits Endpoints

| Method | Endpoint | Description | Features |
|--------|----------|-------------|----------|
| `POST` | `/api/credits/purchase` | Create credit purchase checkout session | Stripe Checkout, metadata |
| `GET` | `/api/credits/purchases` | List user credit purchases | Pagination |
| `GET` | `/api/credits/{id}` | Show specific credit purchase | Policy-protected |

### 📋 Postman Collection

**Import ready collection**: [`docs/postman_collection.json`](./docs/postman_collection.json)

- ✅ All endpoints with examples
- ✅ Environment variables configured  
- ✅ Validation error examples
- ✅ Base URL: `http://localhost:8080`

## 🏗️ Architecture

**Design Pattern**: Model-View-Controller (MVC)  
**API Style**: RESTful with resource transformations  
**Database**: MySQL 8.0 with Eloquent ORM and persistent storage  
**Testing**: Feature + Unit tests with factories  

📖 **Detailed Architecture**: [docs/architecture.md](./docs/architecture.md)

### 1. API Response - Pet List with Pagination

```json
{
  "data": [
    {
      "id": 1,
      "name": "Buddy",
      "species": "Dog", 
      "breed": "Golden Retriever",
      "age": 3,
      "owner_name": "John Smith"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 3,
    "per_page": 15
  }
}
```

### 2. Single Pet with Appointments Include

```json
{
  "data": {
    "id": 1,
    "name": "Buddy",
    "species": "Dog",
    "appointments": [
      {
        "id": 1,
        "title": "Annual Checkup",
        "scheduled_at": "2025-12-15T14:30:00Z",
        "status": "upcoming"
      }
    ]
  }
}
```

## 🧪 Testing & Quality

```bash
# Run all tests
docker-compose exec app php artisan test

# Code style check
docker-compose exec app ./vendor/bin/pint

# Static analysis  
docker-compose exec app ./vendor/bin/phpstan analyse

# Optional local Stripe webhook simulation
./scripts/stripe-webhook-sim.sh --forward-to http://localhost/api/webhooks/stripe
```

**Current Coverage**: 86 tests • 555 assertions • 100% pass rate

## 📁 Project Structure

```bash
src/
├── app/
│   ├── Http/Controllers/     # API controllers
│   ├── Http/Requests/       # Form validation
│   ├── Http/Resources/      # API transformations  
│   ├── Models/              # Eloquent models
│   └── Services/            # Business logic services
├── database/
│   ├── factories/           # Test data factories
│   ├── migrations/          # Database schema
│   └── seeders/            # Demo data
├── tests/
│   └── Feature/            # API integration tests
└── routes/
    └── api.php             # API routes
```

## 🐳 Docker Services

- **app**: PHP 8.3 + Laravel application  
- **web**: Nginx reverse proxy
- **db**: MySQL 8.0 database with persistent storage

**Ports**:

- API: `http://localhost:8080`
- MySQL: `localhost:3307` (host access)
- App direct: `http://localhost:9000` (development)

**Database Configuration**:

- Database: `petcare`
- User: `petuser`
- Password: `petpass`
- Host: `db` (internal) / `localhost:3307` (external)

## 💡 Development Notes

This is an **educational project** demonstrating modern Laravel development. It's not intended for production use but showcases:

- Clean API design patterns
- Comprehensive validation strategies  
- Docker containerization best practices
- Test-driven development approaches
- Laravel 12 feature utilization

Additional notes:

- Gift price is enforced on the server from `GiftType.cost_in_credits`; client-supplied amounts are ignored.
- Wallet debits and credit purchases run in DB transactions to ensure atomic updates.
- Credit purchase sessions expiring are marked as failed by webhook handler.
- Admin gift type create/update requires numeric `cost_in_credits`; create defaults to 100 if omitted.
- Pet restore now uses policy-based authorization; admins can restore.
- GDPR deletion job purges wallets, credit purchases, and credit transactions.

## 🤝 Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature/amazing-feature`
3. Run tests: `docker-compose exec app php artisan test`
4. Commit changes: `git commit -m 'Add amazing feature'`
5. Open Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
