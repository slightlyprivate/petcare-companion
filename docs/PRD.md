# PetCare Companion — Product Requirements Document (PRD)

## Context

PetCare Companion is a comprehensive Laravel + MySQL application designed to demonstrate
production-quality PHP skills for a backend engineering role emphasizing MVC architecture, RESTful
APIs, authentication systems, payment processing, and Dockerized deployment.  
This project's purpose is **portfolio credibility**—to showcase clean, modern PHP practices within a
complete domain: pet management, appointment scheduling, user authentication, and a virtual gift
economy system with credit-based gifting.

## Key Points

- **Primary Goal:** Deliver a polished, working application that highlights strong PHP, MySQL,
  Docker, and modern web development fundamentals.
- **Scope:** Authenticated CRUD APIs for managing pets and appointments, OTP-based authentication,
  role-based access control, and a virtual gift economy system with credit purchases and gifting.
- **Target User:** Developer recruiter or hiring manager evaluating code style, organization,
  fluency, and understanding of modern web application architecture.
- **Technical Stack:**
  - PHP 8.3
  - Laravel 12
  - MySQL 8
  - Laravel Sanctum (API Authentication)
  - Stripe (Credit Purchase Processing)
  - Nginx
  - Docker & Docker Compose
  - Postman Collection for API demonstration

## Requirements

### Functional Requirements

#### Authentication System

- **OTP-based Authentication**
  - Email-based OTP request system
  - Time-limited OTP codes (configurable expiration)
  - Secure token generation via Laravel Sanctum
  - User registration/login flow without passwords
  - Protected API endpoints requiring valid tokens

#### Pet Management

- **Pets (User-owned resources)**
  - Create, list, show, update, delete pets
  - Attributes: name, species, breed, birth_date, owner_name
  - Age calculation based on birth_date
  - Upcoming/past appointment relationships
  - Pagination, filtering, and sorting capabilities

#### Appointment Management

- **Appointments (Linked to pets via FK)**
  - Create, list, show, update, delete appointments
  - Attributes: pet_id, title, scheduled_at, notes
  - Status filtering (upcoming/past)
  - Date range filtering
  - Search functionality (title/notes)
  - Sorting capabilities

#### Gift Economy System

- **Virtual Credit System**
  - Users purchase virtual credits via Stripe (retains existing payment infrastructure)
  - Credits stored on user's wallet account
  - Minimum purchase: 10 credits, no real-world currency outside platform
  - Full purchase history and transaction logging
  - Wallet balance tracking and management

- **Virtual Gifting**
  - Users send symbolic gifts to public pets using credits
  - Gift system supports multiple gifts per pet (no restrictions)
  - Gifts can include optional messages and metadata
  - Gift history and tracking per user and pet
  - Receipt generation for credit purchases and gifts sent
  - Pet-specific gift analytics and popularity metrics

### Non-Functional Requirements

- **Architecture:** Clean MVC, RESTful design, JSON resources, Form Request validation
- **Security:**
  - OTP-based authentication with Sanctum
  - Input validation and sanitization
  - Rate limiting on auth and gift operation endpoints
  - CSRF protection (for future UI)
  - Secure token storage and management
  - Credit transaction logging for audit trail
- **Data Integrity:** FK constraints, cascade delete on appointments, validated data types
- **Performance:**
  - Pagination on list endpoints
  - Eager loading for includes
  - Database indexing on frequently queried fields
  - Query optimization with scopes
- **Maintainability:**
  - Dockerized environment with single-command setup
  - PSR-12 code standards
  - Comprehensive test coverage
  - Clean separation of concerns
- **Documentation:**
  - README with quick-start and API overview
  - Architecture documentation describing domain model and design decisions
  - Postman collection demonstrating all endpoints
  - API documentation with examples
- **Testing:** Comprehensive Feature tests covering CRUD operations and authentication flows

### Deliverables

- Public GitHub repository (`petcare-companion`)
- Docker Compose environment (`app`, `web`, `db`) with proper permissions
- Source code under `src/` following Laravel conventions
- `README.md` and `docs/architecture.md` with current system design
- Postman/Insomnia collection (`docs/api.postman_collection.json`) including auth flows
- Comprehensive test suite with multiple Feature tests
- Clean commit history and professional repository description
- Code quality tools configuration (PHPStan, Pint)

## Success Criteria

- App runs from scratch with 3 commands (`docker compose up`, migrate, seed)
- All authenticated endpoints reachable and validated via Postman
- OTP authentication flow works end-to-end
- Tests pass without modification using `docker-compose exec app php artisan test`
- Code passes static analysis and formatting checks
- README and architecture docs are readable and professional
- Demonstrates strong command of PHP/Laravel conventions, security, and Docker setup

## Architecture Decisions

### Current Implementation

- **Authentication:** OTP-based system using Laravel Sanctum for stateless API tokens
- **Database:** MySQL with proper foreign key constraints and indexing
- **API Design:** RESTful with consistent JSON responses and proper HTTP status codes
- **Validation:** Centralized in Form Request classes with detailed error responses
- **Testing:** Feature tests covering critical user flows and edge cases
- **Code Quality:** Automated formatting (Pint) and static analysis (PHPStan)

### Planned Enhancements

- **Payment Processing:** Stripe integration for virtual credit purchases
- **User Roles:** Admin/user role system using enum-based roles
- **Enhanced Security:** API rate limiting, request logging, audit trails
- **Performance:** Query optimization, caching layer, pagination improvements
- **UI Layer:** Optional Blade-based frontend for demonstration

### Technical Standards

- PSR-12 code formatting enforced via Laravel Pint
- Static analysis via PHPStan for type safety
- Docker-first development with consistent container execution
- Laravel best practices: Service Providers, Policies, Resources, Factories
- Database design: Proper relationships, constraints, and indexing

## Data Model

### Core Entities

1. **User**: Email-based authentication, Sanctum tokens
2. **Otp**: Time-limited verification codes for authentication
3. **Pet**: User-owned pets with calculated age and appointment relationships
4. **Appointment**: Time-scheduled events linked to pets with filtering capabilities

### Future Entities (Planned)

1. **Gift**: Virtual gift records representing gifts sent to pets
2. **Wallet**: User credit wallet with balance tracking
3. **CreditTransaction**: Transaction history for credit purchases and usage
4. **PaymentMethod**: Stripe-managed payment sources for credit purchases

### Relationships

- User → Pets (one-to-many with user ownership)
- User → Wallet (one-to-one with balance tracking)
- User → Gifts (one-to-many for gifts sent)
- Pet → Appointments (one-to-many with cascade delete)
- Pet → Gifts (one-to-many for gifts received)
- Wallet → CreditTransaction (one-to-many for transaction history)

## Security Considerations

- OTP codes expire within configurable timeframe
- Sanctum tokens provide stateless authentication
- Rate limiting on sensitive endpoints (auth requests)
- Input validation on all user-provided data
- SQL injection protection via Eloquent ORM
- XSS protection in JSON responses

## Development Workflow

1. **Setup:** Single command Docker environment startup
2. **Development:** Docker-based Laravel commands for consistency
3. **Testing:** Comprehensive Feature test coverage
4. **Quality:** Automated code formatting and static analysis
5. **Documentation:** Living documentation that reflects actual implementation

## Next Steps

### Phase 1 (Current)

- ✅ Complete Models, Controllers, Resources, and Routes
- ✅ Implement OTP-based authentication system
- ✅ Comprehensive test coverage
- ✅ API documentation and Postman collection
- ✅ Stripe payment integration for credit purchases
- ✅ Virtual gift economy system with wallets and credits
- ✅ Gift transaction logging and history

### Phase 2 (Planned)

- [ ] Premium gift types with special effects/badges
- [ ] Gift leaderboards and pet popularity rankings
- [ ] Bulk credit pack discounts and subscription tiers
- [ ] Admin role and management capabilities
- [ ] Enhanced filtering and search features

### Phase 3 (Future)

- [ ] Optional Blade UI for demonstration
- [ ] Performance optimizations and caching
- [ ] Advanced reporting and analytics
- [ ] Mobile-friendly API enhancements

## References

- Laravel 12 Documentation
- Laravel Sanctum Authentication Guide
- Stripe API Documentation
- MySQL 8 Foreign Keys and Indexing Guide
- Docker Compose Reference
- PSR-12 Code Style Standard
- PHPStan Static Analysis Guide
