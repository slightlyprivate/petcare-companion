target "common" {
  platforms = ["linux/amd64"]
}

target "app" {
  inherits   = ["common"]
  context    = "."
  dockerfile = "docker/app/Dockerfile"
  target     = "runner"
}

target "web" {
  inherits   = ["common"]
  context    = "."
  dockerfile = "docker/web/Dockerfile"
}

target "ui" {
  inherits   = ["common"]
  context    = "."
  dockerfile = "docker/ui/Dockerfile"
}

target "pwa" {
  inherits   = ["common"]
  context    = "."
  dockerfile = "docker/pwa/Dockerfile"
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
  targets = ["app-dev", "web", "ui", "pwa"]
}
