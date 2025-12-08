# Fast, single-arch builds for everyday development and staging.
# Multi-arch builds are isolated to the "all-multiarch" group.

# ============================================================================
# Single-arch target (amd64 only) — used by everyday build workflows
# ============================================================================
target "amd64" {
  platforms = ["linux/amd64"]
}

# ============================================================================
# Multi-arch target (amd64 + arm64) — used by release-multiarch workflow
# ============================================================================
target "multiarch" {
  platforms = ["linux/amd64", "linux/arm64"]
}

# ============================================================================
# App target (runner stage) — fast, amd64-only by default
# ============================================================================
target "app" {
  inherits   = ["amd64"]
  context    = "."
  dockerfile = "docker/app/Dockerfile"
  target     = "runner"
}

# ============================================================================
# Web target — fast, amd64-only by default
# ============================================================================
target "web" {
  inherits   = ["amd64"]
  context    = "."
  dockerfile = "docker/web/Dockerfile"
}

# ============================================================================
# UI target — fast, amd64-only by default
# ============================================================================
target "ui" {
  inherits   = ["amd64"]
  context    = "."
  dockerfile = "docker/ui/Dockerfile"
}

# ============================================================================
# PWA target — fast, amd64-only by default
# ============================================================================
target "pwa" {
  inherits   = ["amd64"]
  context    = "."
  dockerfile = "docker/pwa/Dockerfile"
}

# ============================================================================
# App-dev target (dev stage) — fast, amd64-only (development use only)
# ============================================================================
target "app-dev" {
  inherits   = ["amd64"]
  context    = "."
  dockerfile = "docker/app/Dockerfile"
  target     = "dev"
}

# ============================================================================
# Multi-arch variants — for release workflow only
# ============================================================================
target "app-multiarch" {
  inherits   = ["multiarch"]
  context    = "."
  dockerfile = "docker/app/Dockerfile"
  target     = "runner"
}

target "web-multiarch" {
  inherits   = ["multiarch"]
  context    = "."
  dockerfile = "docker/web/Dockerfile"
}

target "ui-multiarch" {
  inherits   = ["multiarch"]
  context    = "."
  dockerfile = "docker/ui/Dockerfile"
}

target "pwa-multiarch" {
  inherits   = ["multiarch"]
  context    = "."
  dockerfile = "docker/pwa/Dockerfile"
}

# ============================================================================
# Groups
# ============================================================================

# Fast everyday build — amd64 only, ~6–10 minutes
group "all" {
  targets = ["app", "web", "ui", "pwa"]
}

# Development builds — amd64 only, includes dev stage
group "develop" {
  targets = ["app-dev", "web", "ui", "pwa"]
}

# Multi-arch release build — amd64 + arm64, ~20–30 minutes (run separately)
group "all-multiarch" {
  targets = ["app-multiarch", "web-multiarch", "ui-multiarch", "pwa-multiarch"]
}
