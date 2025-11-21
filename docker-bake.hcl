variable "REGISTRY" {
  default = "ghcr.io/slightlyprivate"
}

target "common" {
  platforms = ["linux/amd64", "linux/arm64"]
}

target "app" {
  inherits   = ["common"]
  context    = "."
  dockerfile = "docker/app/Dockerfile"
  target     = "runner"
  tags = [
    "${REGISTRY}/petcare-companion-app:latest",
    "${REGISTRY}/petcare-companion-app:prod",
  ]
  cache-from = ["type=registry,ref=${REGISTRY}/petcare-companion-app:buildcache"]
  cache-to   = ["type=registry,ref=${REGISTRY}/petcare-companion-app:buildcache,mode=max"]
}

target "web" {
  inherits   = ["common"]
  context    = "."
  dockerfile = "docker/web/Dockerfile"
  tags = [
    "${REGISTRY}/petcare-companion-web:latest",
    "${REGISTRY}/petcare-companion-web:prod",
  ]
  cache-from = ["type=registry,ref=${REGISTRY}/petcare-companion-web:buildcache"]
  cache-to   = ["type=registry,ref=${REGISTRY}/petcare-companion-web:buildcache,mode=max"]
}

target "ui" {
  inherits   = ["common"]
  context    = "."
  dockerfile = "docker/ui.Dockerfile"
  tags = [
    "${REGISTRY}/petcare-companion-ui:latest",
    "${REGISTRY}/petcare-companion-ui:prod",
  ]
  cache-from = ["type=registry,ref=${REGISTRY}/petcare-companion-ui:buildcache"]
  cache-to   = ["type=registry,ref=${REGISTRY}/petcare-companion-ui:buildcache,mode=max"]
}

group "all" {
  targets = ["app", "web", "ui"]
}
