# Symfony Docker


## Getting Started

1. Run `docker compose build --no-cache` to build fresh images
2. Run `docker compose up --pull always -d --wait` to set up and start a fresh Symfony project
3. Run `docker compose down --remove-orphans` to stop the Docker containers.
4. Run `docker compose exec php bin/console composer install` to install dependencies.

5. Open `https://localhost/api` to see api platform project.
6. Open `https://localhost:8083` to open pgAdmin.


## Features
1. make api requests at `/api/^`
