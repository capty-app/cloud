# Capty Cloud

A small, **self-hostable** Laravel app for managing galleries of images and videos. Single Docker image, SQLite by default, with an admin dashboard and a simple upload API.

Built with Laravel 13, Inertia 2, React 19, Tailwind v4, and shadcn/ui.

## Quick start (Docker)

```bash
docker run -d \
  --name capty-cloud \
  -p 8080:80 \
  -v $(pwd)/data:/data \
  -e APP_URL=http://localhost:8080 \
  captyapp/cloud:latest
```

Open <http://localhost:8080> and follow the `/setup` page to create the first admin account.

## Local development

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
composer run dev
```

`composer run dev` runs the dev server, Vite, queue worker, and log watcher together.

## Documentation

Full docs live in [`docs/`](docs/) and render at `/docs` inside the running app.
