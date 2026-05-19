# Storage drivers

Uploaded files are written through Laravel's filesystem layer, so you can use any supported driver simply by setting environment variables. By default the app uses **local storage** on the mounted `/data/storage` volume, which is fully self-hosted with no external dependencies.

The driver in use is whatever `FILESYSTEM_DISK` points at, configured in `config/filesystems.php`. The defaults below ship with the app.

## Local (default)

```env
FILESYSTEM_DISK=local
```

Files are stored at `/data/storage/galleries/{gallery_id}/{short_code}.{ext}` inside the container. Mount `/data` as a Docker volume.

Pros: zero external setup, full self-host.
Cons: tied to the host where the container runs.

## S3 (AWS, R2, MinIO, Backblaze, Wasabi, …)

S3-compatible storage. Set:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
AWS_USE_PATH_STYLE_ENDPOINT=false
# Optional, for non-AWS S3-compatible services:
AWS_ENDPOINT=https://s3.eu-central-003.backblazeb2.com
```

For **MinIO**:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=capty
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
```

For **Cloudflare R2**:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=auto
AWS_BUCKET=capty
AWS_ENDPOINT=https://<accountid>.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

The S3 driver requires the `league/flysystem-aws-s3-v3` adapter, which is included in the image.

## FTP / SFTP

Add the corresponding Flysystem package if not bundled, then point `FILESYSTEM_DISK` at an `ftp` or `sftp` disk defined in `config/filesystems.php`. Example for SFTP:

```env
FILESYSTEM_DISK=sftp
SFTP_HOST=sftp.example.com
SFTP_USERNAME=capty
SFTP_PRIVATE_KEY=/data/secrets/id_rsa
SFTP_ROOT=/uploads
```

## How files are served

The app **never serves files directly** from storage. It streams them through Laravel via:

- `/f/{short_code}` — original file
- `/t/{short_code}` — thumbnail (images only)

This means:

- Private galleries are properly access-checked on every request.
- Switching storage drivers doesn't change any URL.
- For very high traffic you may want to put a CDN in front, or change the controller to issue temporary signed URLs (S3 / R2 support this natively via `Storage::temporaryUrl()`).

## Thumbnails

When an **image** is uploaded, a 600×600 max thumbnail is generated in the background (queue worker) and stored on the same disk as `{path}_thumb.jpg`. Videos do not generate thumbnails — the gallery viewer shows a video icon for them.

If you change `FILESYSTEM_DISK` after the app has been used, old items remain on the previous disk. Items store their disk name (`items.disk`) so they keep working.
