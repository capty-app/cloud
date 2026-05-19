# Getting started

This is a small, self-hosted Laravel app that lets you:

- create **galleries** of images and videos
- accept uploads via a simple **HTTP API** (with per-gallery API tokens)
- share each upload as a **short URL** (either public or sign-in-required)
- let signed-in users **comment** on items
- manage **users and galleries** from an admin dashboard

The app runs as a single Docker image with SQLite and a local storage volume by default. You can switch to any Laravel-supported filesystem driver (S3, MinIO, R2, FTP, SFTP…) via env variables.

## 1. Run it

The fastest way:

```bash
docker run -d \
  -p 8080:80 \
  -v $(pwd)/data:/data \
  --name capty-cloud \
  ghcr.io/your-org/capty-cloud:latest
```

Then open <http://localhost:8080>. On first visit you'll be sent to a `/setup` page where you create the initial admin account.

For a real deployment, see [Deployment](deployment).

## 2. Create a gallery

After signing in as admin:

1. Go to **Galleries → New gallery**.
2. Pick a name and slug. The slug becomes the URL path: `/g/{slug}`.
3. Choose **visibility**:
   - *Public* — anyone with the link can view.
   - *Private* — only signed-in users can view.
4. Set the max upload size and any allowed MIME types (leave blank for "any image or video").
5. Save. You'll see the **API token** and a curl example on the gallery page.

## 3. Upload via the API

Each gallery has its own bearer token. See the [Upload API](api-upload) page for full details.

```bash
curl -X POST "https://your-host/api/galleries/my-gallery/upload" \
  -H "Authorization: Bearer gly_xxxxxxxxxxxxx" \
  -F "file=@photo.jpg"
```

The response includes a short URL (`url`) you can share immediately:

```json
{
  "short_code": "aB3xK9Lm",
  "url": "https://your-host/s/aB3xK9Lm",
  "file_url": "https://your-host/f/aB3xK9Lm",
  "kind": "image",
  ...
}
```

## 4. View, share, comment

- The **gallery page** at `/g/{slug}` shows all items as a grid with a built-in lightbox.
- Each item also has a permalink at `/s/{short_code}` so you can share single items.
- Signed-in users can leave **comments** on items if comments are enabled for the gallery.

## Sections

- [Configuration](configuration) — environment variables and how the app is configured
- [Storage drivers](storage-drivers) — using local / S3 / MinIO / etc.
- [Upload API](api-upload) — endpoints, auth, and response shape
- [User roles & access](roles) — admin vs. user, what each can do
- [Deployment](deployment) — Docker run and docker-compose

> Tip: the docs page you're reading right now is rendered from the markdown files inside the `docs/` folder of this repository. Edit those files to customize this site's documentation for your team.
