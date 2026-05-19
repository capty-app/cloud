<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Tables\GalleryTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GalleryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/galleries/index', [
            'galleries' => GalleryTable::make(Gallery::query())->paginate(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/galleries/edit', [
            'gallery' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name']);
        $data['api_token'] = Gallery::generateToken();
        $data['created_by'] = $request->user()->id;

        $gallery = Gallery::create($data);

        return redirect()
            ->route('admin.galleries.show', $gallery)
            ->with('success', 'Gallery created.');
    }

    public function show(Gallery $gallery): Response
    {
        $gallery->load(['items' => fn ($q) => $q->latest()]);

        return Inertia::render('admin/galleries/show', [
            'gallery' => $this->serializeGallery($gallery, withToken: true),
            'items' => $gallery->items->map(fn ($i) => [
                'id' => $i->id,
                'short_code' => $i->short_code,
                'kind' => $i->kind,
                'mime' => $i->mime,
                'size' => $i->size,
                'thumb_url' => $i->thumbUrl(),
                'file_url' => $i->fileUrl(),
                'viewer_url' => $i->viewerUrl(),
                'original_name' => $i->original_name,
                'created_at' => $i->created_at?->toIso8601String(),
            ]),
            'upload_endpoint' => url("/api/galleries/{$gallery->slug}/upload"),
        ]);
    }

    public function edit(Gallery $gallery): Response
    {
        return Inertia::render('admin/galleries/edit', [
            'gallery' => $this->serializeGallery($gallery),
        ]);
    }

    public function update(Request $request, Gallery $gallery): RedirectResponse
    {
        $data = $this->validated($request, $gallery->id);
        if ($data['slug'] !== $gallery->slug) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $gallery->id);
        }
        $gallery->update($data);

        return redirect()
            ->route('admin.galleries.show', $gallery)
            ->with('success', 'Gallery updated.');
    }

    public function destroy(Gallery $gallery): RedirectResponse
    {
        $gallery->delete();

        return redirect()
            ->route('admin.galleries.index')
            ->with('success', 'Gallery deleted.');
    }

    public function rotateToken(Gallery $gallery): RedirectResponse
    {
        $gallery->update(['api_token' => Gallery::generateToken()]);

        return back()->with('success', 'API token rotated.');
    }

    private function serializeGallery(Gallery $gallery, bool $withToken = false): array
    {
        return [
            'id' => $gallery->id,
            'name' => $gallery->name,
            'slug' => $gallery->slug,
            'description' => $gallery->description,
            'visibility' => $gallery->visibility,
            'max_size_mb' => (int) round($gallery->max_size_bytes / 1024 / 1024),
            'allowed_mimes' => $gallery->allowed_mimes,
            'allowed_mimes_raw' => $gallery->allowed_mimes ? implode(', ', $gallery->allowed_mimes) : '',
            'comments_enabled' => (bool) $gallery->comments_enabled,
            'public_url' => route('gallery.show', $gallery->slug),
            'api_token' => $withToken ? $gallery->api_token : null,
            'created_at' => $gallery->created_at?->toIso8601String(),
        ];
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/'],
            'description' => ['nullable', 'string'],
            'visibility' => ['required', 'in:public,private'],
            'allowed_mimes_raw' => ['nullable', 'string'],
            'max_size_mb' => ['required', 'integer', 'min:1', 'max:10240'],
            'comments_enabled' => ['nullable', 'boolean'],
        ]);

        $mimes = collect(preg_split('/[\s,]+/', (string) ($data['allowed_mimes_raw'] ?? '')))
            ->filter()
            ->values()
            ->all();

        return [
            'name' => $data['name'],
            'slug' => Str::slug($data['slug'] ?? $data['name']),
            'description' => $data['description'] ?? null,
            'visibility' => $data['visibility'],
            'allowed_mimes' => empty($mimes) ? null : $mimes,
            'max_size_bytes' => $data['max_size_mb'] * 1024 * 1024,
            'comments_enabled' => (bool) ($data['comments_enabled'] ?? false),
        ];
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $candidate = $slug;
        $i = 2;
        while (Gallery::where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $slug.'-'.$i++;
        }

        return $candidate;
    }
}
