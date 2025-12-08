# GitHub Actions CI/CD Workflow

## Triggers

- Push/PR to main/develop.

## Jobs

1. **Tests:** PHPUnit suite.
2. **Code Style:** Laravel Pint.
3. **API Docs:** Scribe generation.

## Environment

- PHP 8.3, MySQL.

## Local Mimic

```bash
php artisan test
./vendor/bin/pint --test
php artisan scribe:generate
```

## Badges

[![CI](https://github.com/.../badge.svg)](...)
