# Configuration

All configuration is via environment variables (the standard Laravel `.env` file). The Docker image reads them at boot.

## Required

| Variable | Description |
| --- | --- |
| `APP_KEY` | Laravel app key. Auto-generated on first boot if missing. |
| `APP_URL` | The public URL of the app, e.g. `https://gallery.example.com`. **Set this in production** — it's what builds the short URLs returned by the upload API. |

## Application

| Variable | Default | Notes |
| --- | --- | --- |
| `APP_NAME` | `Capty Cloud` | Shown in the dashboard header. |
| `APP_ENV` | `production` | Use `local` for development. |
| `APP_DEBUG` | `false` | Set to `true` only when debugging. |
| `APP_URL` | `http://localhost` | **Must match your public URL** in production. |

## Database

The default is **SQLite**, stored on the persistent volume at `/data/database.sqlite`.

| Variable | Default | Notes |
| --- | --- | --- |
| `DB_CONNECTION` | `sqlite` | Switch to `mysql`/`pgsql` if you prefer. |
| `DB_DATABASE` | `/data/database.sqlite` | For SQLite, the absolute path inside the container. |

If you want MySQL or Postgres instead, set `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` as usual for Laravel.

## Storage

The default storage disk is `local`, writing to `/data/storage` (mounted as a Docker volume). You can switch to any disk Laravel supports.

| Variable | Default | Notes |
| --- | --- | --- |
| `FILESYSTEM_DISK` | `local` | Choose `local`, `public`, `s3`, etc. |

See [Storage drivers](storage-drivers) for the full set of supported drivers and the env variables they expect.

## Session / queue / cache

| Variable | Default | Notes |
| --- | --- | --- |
| `SESSION_DRIVER` | `database` | SQLite-backed by default. |
| `QUEUE_CONNECTION` | `database` | A queue worker runs inside the container via supervisor and processes thumbnail generation. |
| `CACHE_STORE` | `database` | Same. |

## Initial admin

There is no `ADMIN_EMAIL`/`ADMIN_PASSWORD` env shortcut. Instead, on the **first visit** to a fresh installation, you are sent to `/setup` where you create the first admin through a form. As soon as an admin exists, that page disables itself and redirects to `/login`.

If you need to recreate the first admin (e.g. you lost the password and there's no other admin), you can run:

```bash
docker exec -it capty-cloud php artisan tinker
>>> \App\Models\User::where('email', 'you@example.com')->first()->update(['password' => 'new-password']);
```

(Laravel automatically hashes the password when set on a `User`.)

## Volumes

Everything you need to persist lives under `/data` in the container:

```
/data
├── database.sqlite        # SQLite database
└── storage/               # Uploaded files (when FILESYSTEM_DISK=local)
```

Mount it as a Docker volume so it survives container restarts and image upgrades.
