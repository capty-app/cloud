# Deployment

The app ships as a single Docker image. It runs nginx, php-fpm, a queue worker, and the Laravel scheduler under supervisor.

## Quick start (docker run)

```bash
docker run -d \
  --name capty-cloud \
  -p 8080:80 \
  -v $(pwd)/data:/data \
  -e APP_URL=http://localhost:8080 \
  ghcr.io/your-org/capty-cloud:latest
```

Then open <http://localhost:8080>. The first page you see is `/setup` — create your admin account, and you're in.

Everything that needs to persist lives under `/data` in the container:

- `/data/database.sqlite`
- `/data/storage/...` (uploads, when using the default local disk)

If you wipe that volume, you lose everything. If you back it up, you can restore the entire app by mounting it on a new container.

## docker-compose (recommended for real use)

`docker-compose.yml`:

```yaml
services:
  app:
    image: ghcr.io/your-org/capty-cloud:latest
    restart: unless-stopped
    ports:
      - "8080:80"
    environment:
      APP_URL: https://gallery.example.com
      APP_NAME: "Capty Cloud"
      # Default is SQLite at /data/database.sqlite — nothing to set.
      # Switch storage to S3:
      # FILESYSTEM_DISK: s3
      # AWS_ACCESS_KEY_ID: ...
      # AWS_SECRET_ACCESS_KEY: ...
      # AWS_DEFAULT_REGION: us-east-1
      # AWS_BUCKET: my-bucket
    volumes:
      - capty-data:/data

volumes:
  capty-data:
```

Then:

```bash
docker compose up -d
```

## Reverse proxy / TLS

The container serves plain HTTP on port `80`. Put it behind your reverse proxy of choice (Caddy, nginx, Traefik, Cloudflare Tunnel…) for TLS.

**Important:** set `APP_URL` to the public HTTPS URL. The upload API uses this to build the `url` it returns in responses. If `APP_URL` is wrong, your short links will point at the wrong host.

If you're behind a TLS-terminating proxy, that's all you need — Laravel's trusted-proxy middleware is configured to honor `X-Forwarded-*` headers from any proxy.

## Migrations and first boot

On every container start the entrypoint:

1. Generates `APP_KEY` if missing.
2. Creates `/data/database.sqlite` if missing.
3. Runs `php artisan migrate --force` to apply any pending migrations.
4. Caches config & routes.
5. Starts supervisor (nginx + php-fpm + queue worker + scheduler).

This makes upgrades zero-effort — pull the new image and restart the container.

## Health check

The container exposes `/up` (Laravel's built-in health endpoint) which returns `200` when the app is up.

## Backups

For local storage + SQLite (default), back up `/data` and you have everything. For S3-backed storage, back up `/data` (DB only) plus your bucket.

A safe backup recipe (SQLite is durable but a snapshot avoids partial writes):

```bash
docker exec capty-cloud sqlite3 /data/database.sqlite ".backup '/data/backup-$(date +%F).sqlite'"
```

## Resource sizing

The default image is comfortable with **256 MB RAM** for small workloads. Increase if you process many large uploads concurrently (the thumbnail job decodes images in memory).
