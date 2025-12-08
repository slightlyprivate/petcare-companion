# PetCare Companion — Agent Instructions

## Context

This document defines how autonomous agents or AI copilots (e.g., GitHub Copilot Chat or local build
assistants) should behave when working on the **PetCare Companion** repository.

## Key Points

- Primary objective: **streamline development tasks** (scaffold, refactor, test, document) without
  altering project intent.
- Code standards: **PSR-12**, current Laravel best practices, atomic commits, no generated secrets.

## Guardrails

- No hard-coded secrets or credentials; use environment variables for configs.
- No unreviewed dependencies or external APIs.
- Keep total container memory footprint minimal (<512 MB app).
- Retain project educational and demonstrative intent — **not** production service.

## Agent Roles

### 1. **Builder Agent**

**Purpose:** Implement and maintain Laravel codebase.  
**Tasks:**

- Create Models, Controllers, Requests, Resources, and Routes per PRD.
- Maintain database migrations and seeders.
- Use dependency injection and typed properties.
- Avoid adding third-party packages without explicit instruction.
- Generate Feature tests with Laravel's built-in testing framework.
- Use self-documenting functions; minimize comments.
- **Execute all Laravel commands via `docker-compose exec app` for consistency.**

### 2. **Refactor Agent**

**Purpose:** Optimize and enforce conventions post-build.  
**Tasks:**

- Run static analysis (`docker-compose exec app ./vendor/bin/phpstan`,
  `docker-compose exec app ./vendor/bin/pint`) and propose minor style corrections.
- Refine controller logic for single responsibility.
- Ensure validation rules are centralized in Form Requests.
- Confirm pagination and relationship loading are efficient.
- Verify Eloquent models define `$fillable`, `$casts`, `$with` appropriately.
- **Execute all analysis tools via `docker-compose exec app` commands.**

### 3. **Docs Agent**

**Purpose:** Maintain clarity and professionalism of documentation.  
**Tasks:**

- Update `README.md` when environment or endpoints change.
- Maintain `docs/architecture.md` consistency with implementation.
- Keep API collection in sync with routes.
- Include at least one diagram (Excalidraw, Mermaid, or PNG) showing relationships.
- Audit spelling and grammar.

### 4. **Test Agent**

**Purpose:** Ensure reliability and coverage.  
**Tasks:**

- Maintain minimum 2 Feature tests passing at all times.
- Verify migrations run fresh and seeders load properly.
- Add new tests when endpoints or validation rules change.
- **Execute all test commands via `docker-compose exec app php artisan test` or
  `docker-compose exec app ./vendor/bin/phpunit`.**

### 5. **DevOps Agent**

**Purpose:** Oversee container health and reproducibility.  
**Tasks:**

- Validate Docker Compose builds cleanly with no permission warnings.
- Maintain `.env.example` and note environment expectations.
- Ensure volumes and ports are documented.
- Check build args (UID/GID) match developer defaults.
- Use Compose profiles for multi-environment support (dev/staging/prod).

## Workflow Directives

1. Branch naming convention: `feature/*`, `fix/*`, `docs/*`.
2. Pull Requests must include:
   - Short description.
   - Checklist of affected components.
3. After merge, Refactor Agent runs style and config lint passes.
4. Docs Agent syncs documentation within same PR or follow-up PR.
5. Only squash merges to keep linear history.
