# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project
adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Automatic multi-image Docker builds using **Buildx + Bake**
- Unified GitHub Actions workflow for app/web/ui image publishing
- Versioned image tagging (`VERSION` file → registry tags)
- Release automation using **release-please**
- New Makefile targets for baking, pushing, building, version bumping
- Production-grade Nginx configs for API + SPA UI
- Fully optimized Dockerfiles for app, web, and UI builds
- Container healthchecks across app/web/ui + workers

### Changed

- Reorganized `docker/` directory into `docker/app`, `docker/web`, `docker/ui`, `docker/shared`
- Removed deprecated `prod` tag and replaced with semantic versioned tags
- Normalized build caching to GitHub Actions cache (`type=gha`)
- Standardized multi-arch builds and eliminated redundant Node layers

### Fixed

- CI workflow duplication issues across multiple image pipelines
- Incorrect Dockerfile paths for UI builds (`ui.Dockerfile` → `ui/Dockerfile`)
- Horizon/Worker/Scheduler failing to find `vendor/` in development mode
- Nginx upstream failures when running locally
- PHP extension setup inconsistencies across architectures

---

## [0.2.0] – 2025-11-22

### Added

- Complete semantic versioning + tagging system
- changelog, version bumping, and release automation
- Bakefile (docker-bake.hcl) with centralized targets for app/web/ui
- Unified `build-images.yml` workflow with metadata labels, cache, and version tags
- `make bump-version` for patch/minor/major bumps
- Support for auto-latest tagging when pushing to main

### Changed

- Migrated all per-image workflows into a single orchestrated CI pipeline
- Switched from registry caches to GitHub Actions caches for consistency
- Updated Makefile to compute tags and conditionally apply `latest` on main

---

## [0.1.0] – 2025-11-20

### Added

- Initial production-ready Docker setup for the Laravel app
- Nginx production image (web)
- UI build pipeline scaffold
- docker-compose.prod.yml for multi-service deployment
- Early GitHub Actions CI/CD pipeline
- Laravel + React project foundations (app + ui scaffolding)
