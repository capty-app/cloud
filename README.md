# Capty Cloud

A small, **self-hostable** Laravel app for managing galleries of images and videos. Single Docker image, SQLite by default, with an admin dashboard and a simple upload API.

## Stack

- **Laravel 13** + **Inertia 2** + **React 19** (TypeScript) starter kit
- **shadcn/ui** components, **Tailwind v4**, **Tailwind Typography** for docs
- **Light / Dark / System** theme with live system-scheme tracking (`useAppearance`)
- **[Inertia Table](https://inertia-table.forjed.io/)** for backend-driven tables
- SQLite (default) + local file storage volume; switch storage driver via env (any Laravel-supported disk)

## Features

- First-run **/setup** page creates the initial admin (no public registration).
- Admin manages **galleries** (public/private, comments enabled, allowed MIME types, max size) and **users** (admin/user role).
- Each gallery has a **per-gallery API token** for the **upload endpoint**:
  ```
  POST /api/galleries/{slug}/upload
  Authorization: Bearer gly_xxxxxxxxxxxx
  ```
  Response includes the **short URL** to share.
- Public viewer at `/g/{slug}` (grid + shadcn Dialog lightbox, keyboard nav).
- Per-item permalink at `/s/{code}` with comments (signed-in users only).
- Built-in `/docs` route renders the markdown files in `docs/`.

## Quick start

```bash
docker run -d \
  --name capty-cloud \
  -p 8080:80 \
  -v $(pwd)/data:/data \
  -e APP_URL=http://localhost:8080 \
  capty-cloud:latest
```

Open <http://localhost:8080>. The first page is `/setup` — create your admin and you're in.

For deployment with TLS, see [docs/deployment.md](docs/deployment.md).

## Documentation

Everything is in `docs/` and rendered at `/docs` inside the app:

- [docs/index.md](docs/index.md) — Getting started
- [docs/configuration.md](docs/configuration.md) — Environment variables
- [docs/storage-drivers.md](docs/storage-drivers.md) — Local / S3 / MinIO / R2 / SFTP
- [docs/api-upload.md](docs/api-upload.md) — Upload API reference
- [docs/roles.md](docs/roles.md) — User roles and access
- [docs/deployment.md](docs/deployment.md) — Docker run and docker-compose

## Local development

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
composer run dev
```

This runs `php artisan serve`, `npm run dev`, queue worker, and log watcher together via `concurrently`.

## CI / Release

Two workflows live in `.github/workflows/`:

- **`checks.yml`** — runs on every push and PR to `main`. Three parallel jobs:
  - `php`: Composer install → Pint → Pest with `--min=90` coverage.
  - `node`: npm ci → `types:check` → `lint:check` → `format:check` → `npm run build`.
  - `docker`: build the image, boot it, and probe `/up` and `/setup`.
- **`release.yml`** — manual via *Actions → Release → Run workflow*. Inputs:
  - `version` — e.g. `1.0.0` or `1.1.0-beta.1`.
  - `prerelease` — checkbox to mark the GitHub release as pre-release.
  - `latest` — checkbox to also push the `:latest` image tag.

  The release job bumps the version in `package.json` and `config/app.php`, commits, tags `vX.Y.Z`, creates a GitHub release, then builds **amd64** and **arm64** images in parallel and finally creates a multi-arch manifest at `captyapp/cloud:X.Y.Z` (and `:latest` if the checkbox is on).

**Required repo secrets** (Settings → Secrets and variables → Actions):

| Secret | Used by |
| --- | --- |
| `DOCKERHUB_USERNAME` | release workflow |
| `DOCKERHUB_TOKEN` | release workflow (Docker Hub access token, not your account password) |

The release workflow also needs **Read and write permissions** under Settings → Actions → General → Workflow permissions so it can push the version-bump commit and tag.
