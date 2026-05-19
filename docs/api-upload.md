# Upload API

A single endpoint to push media into a gallery from any external system (scripts, n8n, Zapier, CI, mobile clients…).

## Endpoint

```
POST /api/galleries/{slug}/upload
```

- `{slug}` is the slug shown on the gallery's admin page (`/g/{slug}`).
- Content type: `multipart/form-data`.
- Field name: `file` (required).

## Authentication

Each gallery has its own bearer token, visible (and rotatable) on the gallery's admin page.

```
Authorization: Bearer gly_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

`X-Api-Token` is also accepted as an alternative header.

The token is **per gallery** — it only authorizes uploads to that specific gallery. Rotate it from the admin page if it leaks.

## Validation

The gallery's configuration controls what is allowed:

- **Max size** — defaults to 100 MB, configurable per gallery.
- **MIME types** — defaults to "all images and videos" (`image/*` or `video/*`). The admin can specify an explicit list like `image/jpeg, image/png, video/mp4` or wildcards like `image/*`.

If the file violates either, the API returns `422` with details.

## Response

`201 Created`:

```json
{
  "id": 12,
  "short_code": "aB3xK9Lm",
  "url": "https://your-host/s/aB3xK9Lm",
  "short_url": "https://your-host/s/aB3xK9Lm",
  "file_url": "https://your-host/f/aB3xK9Lm",
  "thumb_url": "https://your-host/t/aB3xK9Lm",
  "gallery_url": "https://your-host/g/my-gallery",
  "mime": "image/jpeg",
  "size": 482912,
  "kind": "image",
  "original_name": "photo.jpg"
}
```

- `url` / `short_url` — public viewer page (a permalink for this single item).
- `file_url` — direct file URL (the raw image/video).
- `thumb_url` — thumbnail URL (for images; `null` for videos until generated).
- `gallery_url` — the gallery this item belongs to.

> Note: thumbnails are generated asynchronously by the queue worker, so `thumb_url` for images may briefly resolve to the original file until the thumbnail job runs. The gallery viewer handles this transparently.

## Errors

| Code | When |
| --- | --- |
| `401` | Missing or wrong bearer token. |
| `404` | Gallery slug doesn't exist. |
| `422` | File missing, too big, or MIME not allowed. |

Example: too-large file:

```json
{ "error": "File exceeds max size.", "max_size_bytes": 104857600 }
```

## Examples

### curl

```bash
curl -X POST "https://your-host/api/galleries/family-photos/upload" \
  -H "Authorization: Bearer gly_xxxxxxxxx" \
  -F "file=@/path/to/photo.jpg"
```

### Node (fetch)

```js
const fd = new FormData();
fd.append('file', new Blob([buffer], { type: 'image/jpeg' }), 'photo.jpg');

const res = await fetch('https://your-host/api/galleries/family-photos/upload', {
  method: 'POST',
  headers: { Authorization: `Bearer ${process.env.GALLERY_TOKEN}` },
  body: fd,
});
const data = await res.json();
console.log(data.url); // share this short link
```

### Python (requests)

```python
import requests
r = requests.post(
    'https://your-host/api/galleries/family-photos/upload',
    headers={'Authorization': f'Bearer {token}'},
    files={'file': open('photo.jpg', 'rb')},
)
print(r.json()['url'])
```

## Visibility

The visibility setting on the gallery controls who can open the returned `url`:

- **Public** gallery — anyone with the link can view.
- **Private** gallery — visitors are sent to `/login` first. Only authenticated users (admin or user role) can open the page.

The upload token is unaffected — it is what *uploads*, not what *views*.
