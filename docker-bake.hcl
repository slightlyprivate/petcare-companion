target "common" {
  platforms = ["linux/amd64", "linux/arm64"]
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

group "all" {
  targets = ["app", "web", "ui"]
}
