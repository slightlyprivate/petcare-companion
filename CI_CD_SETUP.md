# GitHub Actions CI/CD Workflow

This document describes the automated CI/CD pipeline for PetCare Companion.

## Workflow Triggers

The CI/CD pipeline is automatically triggered on:

- **Push events** to `main` or `develop` branches
- **Pull requests** against `main` or `develop` branches

## Jobs

### 1. **Tests** (Matrix: PHP 8.2, 8.3)

Runs the complete PHPUnit test suite across multiple PHP versions.

**Services:**

- MySQL 8.0 (for database tests)

**Steps:**

1. Checkout code
2. Setup PHP with required extensions
3. Install Composer dependencies
4. Generate app encryption key for testing environment
5. Execute test suite (`php artisan test`)

**Configuration:**

- Matrix testing across PHP 8.2 and 8.3
- In-memory SQLite database for tests (configured in `phpunit.xml`)
- XDebug coverage support

### 2. **Code Style** (Laravel Pint)

Validates code formatting against PSR-12 standards.

**Steps:**

1. Checkout code
2. Setup PHP 8.3
3. Install Composer dependencies
4. Run Laravel Pint in test mode (`./vendor/bin/pint --test`)

**Purpose:**

- Ensures all code follows PSR-12 style guide
- Fails the workflow if formatting issues are found
- Use locally: `cd src && ./vendor/bin/pint` to auto-fix issues

### 3. **API Documentation** (Scribe)

Generates and commits API documentation to the repository.

**Runs only on:**

- Push events to `main` or `develop` branches

**Steps:**

1. Checkout code
2. Setup PHP 8.3
3. Install Composer dependencies
4. Generate documentation (`php artisan scribe:generate`)
5. Commit and push changes if there are updates

**Purpose:**

- Keeps Postman collection and API documentation in sync
- Auto-updates docs on each push to protected branches
- Maintains `docs/` directory with latest API specifications

## Environment Variables

The workflow uses GitHub Actions' built-in environment configuration:

- `APP_ENV=testing` - Set via `phpunit.xml`
- `DB_CONNECTION=sqlite` - In-memory database for tests
- Standard Laravel test environment setup

## Failure Handling

The workflow will fail if:

1. **Tests fail** - Any test assertion failure stops the pipeline
2. **Code style violations** - PSR-12 violations detected by Pint
3. **Missing PHP extensions** - Required extensions not available

## Local Development

To mimic the CI environment locally:

```bash
# Run tests across PHP 8.2 and 8.3
cd src
php artisan test

# Check code style
./vendor/bin/pint --test

# Auto-fix style issues
./vendor/bin/pint

# Generate API docs
php artisan scribe:generate
```

## Customization

To modify the workflow:

1. Edit `.github/workflows/ci.yml`
2. Commit and push changes
3. GitHub Actions will use the updated workflow on next trigger

### Common customizations

**Add matrix PHP version:**

```yaml
matrix:
  php: ["8.2", "8.3", "8.4"]
```

**Add additional extensions:**

```yaml
extensions: curl, dom, fileinfo, libxml, mbstring, pcntl, pdo, sqlite, pdo_mysql, redis, soap, zip, gd
```

**Require services:**

```yaml
services:
  redis:
    image: redis:latest
    options: >-
      --health-cmd "redis-cli ping"
      --health-interval 10s
      --health-timeout 5s
      --health-retries 5
    ports:
      - 6379:6379
```

## Status Badges

Add this to your `README.md` to display CI status:

```markdown
[![CI](https://github.com/slightlyprivate/petcare-companion/actions/workflows/ci.yml/badge.svg)](https://github.com/slightlyprivate/petcare-companion/actions/workflows/ci.yml)
```

## Documentation

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Laravel Pint](https://laravel.com/docs/pint)
- [Scribe API Documentation](https://scribe.knuckles.wtf/)
