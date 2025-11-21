# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project
adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.0.0 (2025-11-21)


### Features

* add authorization for credit purchases and gift type management, including factories and tests ([2a55450](https://github.com/slightlyprivate/petcare-companion/commit/2a5545099ea882b07579d927fd9ca2ea82eb84e4))
* add bodyParameters method to request classes for user and donation data ([5cdcc29](https://github.com/slightlyprivate/petcare-companion/commit/5cdcc2910360900f14ba6431ce5a0f613ec7ae5d))
* add data flow and architecture diagrams to architecture overview ([a182340](https://github.com/slightlyprivate/petcare-companion/commit/a1823401274b460ea3256f9a4425080bca3a51b0))
* add Docker Buildx setup step in CI workflow ([fd2f0a1](https://github.com/slightlyprivate/petcare-companion/commit/fd2f0a13462f3dd118e798249ffc3de6a74729d7))
* add documentation environment setup and .env.docs file for API documentation generation ([915e15f](https://github.com/slightlyprivate/petcare-companion/commit/915e15f6d23d1e9d643f215a79d18dee0327f171))
* add GitHub Actions workflow for building and pushing UI image ([eeaf84d](https://github.com/slightlyprivate/petcare-companion/commit/eeaf84d08aeddc026779dacade53ca4e326404b0))
* add GITHUB_TOKEN to auto-generate API documentation commit step ([31f4757](https://github.com/slightlyprivate/petcare-companion/commit/31f475709b5dfa2430ff26e68a259eaf2cd95f1f))
* add initial changelog with project updates and versioning details ([fa325e2](https://github.com/slightlyprivate/petcare-companion/commit/fa325e24eb95fcebac255c4aa45b8fb464e2b751))
* add pages for dashboard, home, login via OTP, pet details, and purchases ([f2d3c1f](https://github.com/slightlyprivate/petcare-companion/commit/f2d3c1f36393ec1da6b57ea94f562735f540658c))
* add pet photo example to documentation ([613ab0b](https://github.com/slightlyprivate/petcare-companion/commit/613ab0b70ae267d553ff18e09d9bd94445f3f119))
* add react-router-dom for routing capabilities ([f2d3c1f](https://github.com/slightlyprivate/petcare-companion/commit/f2d3c1f36393ec1da6b57ea94f562735f540658c))
* complete gifts & credits economy, public reporting, webhook hardening, and compliance APIs ([7b7228d](https://github.com/slightlyprivate/petcare-companion/commit/7b7228d2c43fa09397983fe2878cbdd8ae6f1720))
* configure routes with authentication requirements ([f2d3c1f](https://github.com/slightlyprivate/petcare-companion/commit/f2d3c1f36393ec1da6b57ea94f562735f540658c))
* create reusable components for app shell, buttons, error messages, query boundaries, and spinners ([f2d3c1f](https://github.com/slightlyprivate/petcare-companion/commit/f2d3c1f36393ec1da6b57ea94f562735f540658c))
* create Section component for grouping related content ([2f09f61](https://github.com/slightlyprivate/petcare-companion/commit/2f09f615e5d3b2720251f2a2c7cc30c9b295b48c))
* create TextArea and TextInput components for form inputs ([2f09f61](https://github.com/slightlyprivate/petcare-companion/commit/2f09f615e5d3b2720251f2a2c7cc30c9b295b48c))
* enhance Docker image tagging and summary in build workflow ([c03d5d1](https://github.com/slightlyprivate/petcare-companion/commit/c03d5d1c99c6c8d7b684ad03dbfb565e8313ecc1))
* enhance README with proxy target instructions ([f2d3c1f](https://github.com/slightlyprivate/petcare-companion/commit/f2d3c1f36393ec1da6b57ea94f562735f540658c))
* implement API client and hooks for appointments, authentication, credits, gifts, and pets ([f2d3c1f](https://github.com/slightlyprivate/petcare-companion/commit/f2d3c1f36393ec1da6b57ea94f562735f540658c))
* implement authorization policies for appointments and credit purchases ([2a55450](https://github.com/slightlyprivate/petcare-companion/commit/2a5545099ea882b07579d927fd9ca2ea82eb84e4))
* merge feat/scaffold ([857715d](https://github.com/slightlyprivate/petcare-companion/commit/857715dc2b5cfe2873fa6df9c0b6c31a68398aff))
* restructure Docker setup and optimize build process ([526f4be](https://github.com/slightlyprivate/petcare-companion/commit/526f4be80155cc000c68578787680ca74226d4f8))
* update media upload requests with body parameters and examples ([7b29e9e](https://github.com/slightlyprivate/petcare-companion/commit/7b29e9ed9a0c2b632926d0b6c8fd838e01f9c9c2))


### Bug Fixes

* correct action reference in release workflow and add release manifest ([a019f05](https://github.com/slightlyprivate/petcare-companion/commit/a019f05150fa9a270a5568c8d91adfe6979dd702))
* remove hardcoded APP_KEY from .env.docs ([c3e233c](https://github.com/slightlyprivate/petcare-companion/commit/c3e233c51c989b43f26f8048d6de9e953104e02b))
* remove labels output from Docker metadata action ([10d3316](https://github.com/slightlyprivate/petcare-companion/commit/10d331637383a8bb36e69aca19f58510b8fe2111))
* remove unnecessary line breaks in index.html ([f2d3c1f](https://github.com/slightlyprivate/petcare-companion/commit/f2d3c1f36393ec1da6b57ea94f562735f540658c))
* update release-please configuration to include changelog path ([ae3dfba](https://github.com/slightlyprivate/petcare-companion/commit/ae3dfbae1b415612453cc245b4c9b49e42ae3ece))

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
