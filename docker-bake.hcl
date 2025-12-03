target "common" {
  platforms = ["linux/amd64", "linux/arm64"]
}

target "app" {
  inherits   = ["common"]
  context    = "."
  dockerfile = "docker/app/Dockerfile"
  target     = "runner"
  platforms = ["linux/amd64"]
}

target "web" {
  inherits   = ["common"]
  context    = "."
  dockerfile = "docker/web/Dockerfile"
  platforms = ["linux/amd64"]
}

target "ui" {
  inherits   = ["common"]
  context    = "."
  dockerfile = "docker/ui/Dockerfile"
  platforms = ["linux/amd64"]
}

target "pwa" {
  inherits   = ["common"]
  context    = "."
  dockerfile = "docker/pwa/Dockerfile"
  platforms = ["linux/amd64"]
}

group "all" {
  targets = ["app", "web", "ui", "pwa"]
}

target "app-dev" {
  inherits   = ["common"]
  context    = "."
  dockerfile = "docker/app/Dockerfile"
  target     = "dev"
}

group "develop" {
  targets = ["app", "web", "ui", "pwa"]
}
