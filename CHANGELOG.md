# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project
adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.8.0](https://github.com/slightlyprivate/petcare-companion/compare/0.7.0...0.8.0) (2025-12-04)


### Features

* Add staging and release tags for Docker images in build workflow ([c8d008d](https://github.com/slightlyprivate/petcare-companion/commit/c8d008d009d6a552c7c3d6b4592ee5e6a6eb9e4c))
* Enhance Docker configuration and security settings across multiple services ([b984fdd](https://github.com/slightlyprivate/petcare-companion/commit/b984fdd1d56af558e9de5ebc2b0dccde790c2683))
* Update Docker Compose files to replace 'backend' and 'frontend' networks with 'default' ([eb0bd10](https://github.com/slightlyprivate/petcare-companion/commit/eb0bd109cfb0af5a2088cbac6fa05692e40b4533))
* Update Dockerfile to ensure proper user context for Composer installation in development stage ([90356ce](https://github.com/slightlyprivate/petcare-companion/commit/90356ced7ca1eec556d89d34dcb7426cea16bfe7))

## [0.7.0](https://github.com/slightlyprivate/petcare-companion/compare/0.6.0...0.7.0) (2025-12-02)


### Features

* Update App component to reflect caregiving focus and roadmap ([26d715d](https://github.com/slightlyprivate/petcare-companion/commit/26d715d77064427f154f46f1c3dca7c146d2c752))

## [0.6.0](https://github.com/slightlyprivate/petcare-companion/compare/0.5.0...0.6.0) (2025-12-02)


### Features

* add entrypoint script to set permissions for storage directory ([903c0a3](https://github.com/slightlyprivate/petcare-companion/commit/903c0a391e697cea0d0ed113c7fbb28a23bf8972))
* add logo image to README and update layout ([c9a6fc1](https://github.com/slightlyprivate/petcare-companion/commit/c9a6fc1ccddd25d8ba3a021d29f4e30efce9cf11))
* add versioning to builds and display in app footer ([5064a52](https://github.com/slightlyprivate/petcare-companion/commit/5064a5230dd8e20faea21da12e0c9b8a31d07cd8))
* **deploy:** Implement blue/green deployment strategy for production ([#28](https://github.com/slightlyprivate/petcare-companion/issues/28)) ([63dc5bf](https://github.com/slightlyprivate/petcare-companion/commit/63dc5bfe54cfb5263150d26ac03868a7c7f7ccf1))
* enhance entrypoint scripts for Laravel filesystem preparation and cache management ([7e26c9a](https://github.com/slightlyprivate/petcare-companion/commit/7e26c9a92d9283b7d97dd32d34cca7c2355b74b0))


### Bug Fixes

* correct syntax for setting VITE_APP_VERSION in build workflow ([2f2f09f](https://github.com/slightlyprivate/petcare-companion/commit/2f2f09f7da54a76a3f1917f3ac6bd6eb7648fc3b))
* remove unused AppVersionFooter component from AuthLayout ([5d9f348](https://github.com/slightlyprivate/petcare-companion/commit/5d9f348fc5b1101e31fe662471c0c31afc919dee))
* update image tag patterns for consistency across environments ([ff582a0](https://github.com/slightlyprivate/petcare-companion/commit/ff582a09750867d71a49125b01ea08585abe48f3))

## [0.5.0](https://github.com/slightlyprivate/petcare-companion/compare/0.4.2...0.5.0) (2025-11-23)


### Features

* add @types/node to devDependencies and update tsconfig for type support ([6b36f57](https://github.com/slightlyprivate/petcare-companion/commit/6b36f572f70707fb3763a5be30dc220c8060a6ed))
* update package.json scripts and dependencies, add TypeScript configuration ([#26](https://github.com/slightlyprivate/petcare-companion/issues/26)) ([2aabec5](https://github.com/slightlyprivate/petcare-companion/commit/2aabec57dd5d3e9d514b56949a7d0cb60f29d68b))


### Bug Fixes

* update Vite configuration for server settings ([2aabec5](https://github.com/slightlyprivate/petcare-companion/commit/2aabec57dd5d3e9d514b56949a7d0cb60f29d68b))

## [0.4.2](https://github.com/slightlyprivate/petcare-companion/compare/0.4.1...0.4.2) (2025-11-23)


### Bug Fixes

* **docker:** update Dockerfile for improved PHP app build and nginx configuration ([980821c](https://github.com/slightlyprivate/petcare-companion/commit/980821c315e6dc3012b8962011b5c57ec0618b2a))
* **docker:** update Dockerfiles to pull latest security patches for dependencies ([8131b17](https://github.com/slightlyprivate/petcare-companion/commit/8131b17d0429f13ab54aa1f7baf5a2cb2709353c))
* **proxy:** update Host header to use $proxy_host for API endpoints ([ee4dc69](https://github.com/slightlyprivate/petcare-companion/commit/ee4dc69269bfcdfe18621532d7c93571b16dd42c))
* **ui:** add platforms specification for UI target ([714f6d9](https://github.com/slightlyprivate/petcare-companion/commit/714f6d91d3ac5c8eb93e23b1745a6448dc2c2e3d))

## [0.4.1](https://github.com/slightlyprivate/petcare-companion/compare/0.4.0...0.4.1) (2025-11-22)


### Bug Fixes

* add category to Trivy SARIF uploads for better organization ([4bd0e7a](https://github.com/slightlyprivate/petcare-companion/commit/4bd0e7acc7e6e98d7ebdf145e0b39827f98d1141))

## [0.4.0](https://github.com/slightlyprivate/petcare-companion/compare/v0.3.1...0.4.0) (2025-11-22)


### Features

* add Trivy vulnerability scanning and SARIF upload to CI workflow ([3a11e9a](https://github.com/slightlyprivate/petcare-companion/commit/3a11e9aa938535ca302ee995d80f4bef15faaa03))


### Bug Fixes

* remove unnecessary release-type field from configuration ([b1ef4a6](https://github.com/slightlyprivate/petcare-companion/commit/b1ef4a67998d1a3aa1f875ea342de40eec0fa5da))
* update release type to version in release configuration ([2846320](https://github.com/slightlyprivate/petcare-companion/commit/284632067f16711cfcd78dd92dffca7450090b35))

## [0.3.1](https://github.com/slightlyprivate/petcare-companion/compare/v0.3.0...v0.3.1) (2025-11-22)


### Bug Fixes

* update develop group targets to include only app, web, and ui ([3a061e7](https://github.com/slightlyprivate/petcare-companion/commit/3a061e76440022d5dbbedb51c1ef222ad218c3bc))

## [0.3.0](https://github.com/slightlyprivate/petcare-companion/compare/v0.2.0...v0.3.0) (2025-11-21)


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

* add missing newline at end of release-please configuration file ([a60f389](https://github.com/slightlyprivate/petcare-companion/commit/a60f389bfa622bb22a0c07aa762c408a38c60f30))
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
