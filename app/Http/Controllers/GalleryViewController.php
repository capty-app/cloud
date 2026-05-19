<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GalleryViewController extends Controller
{
    public function show(Request $request, string $slug): \Inertia\Response
    {
        $gallery = Gallery::where('slug', $slug)->firstOrFail();
        $this->authorizeAccess($gallery);

        $items = $gallery->items()->latest()->get()->map(fn (Item $i) => [
            'id' => $i->id,
            'short_code' => $i->short_code,
            'kind' => $i->kind,
            'file_url' => $i->fileUrl(),
            'thumb_url' => $i->thumbUrl(),
            'viewer_url' => $i->viewerUrl(),
            'original_name' => $i->original_name,
            'mime' => $i->mime,
            'size' => $i->size,
        ]);

        return Inertia::render('viewer/gallery', [
            'gallery' => [
                'name' => $gallery->name,
                'slug' => $gallery->slug,
                'description' => $gallery->description,
                'visibility' => $gallery->visibility,
                'comments_enabled' => (bool) $gallery->comments_enabled,
            ],
            'items' => $items,
        ]);
    }

    public function showItem(Request $request, string $code): \Inertia\Response
    {
        $item = Item::where('short_code', $code)->with('gallery', 'comments.user')->firstOrFail();
        $this->authorizeAccess($item->gallery);

        return Inertia::render('viewer/item', [
            'gallery' => [
                'name' => $item->gallery->name,
                'slug' => $item->gallery->slug,
                'visibility' => $item->gallery->visibility,
                'comments_enabled' => (bool) $item->gallery->comments_enabled,
            ],
            'item' => [
                'id' => $item->id,
                'short_code' => $item->short_code,
                'kind' => $item->kind,
                'file_url' => $item->fileUrl(),
                'thumb_url' => $item->thumbUrl(),
                'viewer_url' => $item->viewerUrl(),
                'original_name' => $item->original_name,
                'mime' => $item->mime,
                'size' => $item->size,
            ],
            'comments' => $item->comments->map(fn ($c) => [
                'id' => $c->id,
                'body' => $c->body,
                'created_at' => $c->created_at?->toIso8601String(),
                'user' => [
                    'id' => $c->user->id,
                    'name' => $c->user->name,
                ],
            ]),
        ]);
    }

    public function file(Request $request, string $code): StreamedResponse|Response
    {
        $item = Item::where('short_code', $code)->with('gallery')->firstOrFail();
        $this->authorizeAccess($item->gallery);

        return $this->streamFromDisk($item->disk, $item->path, $item->mime, $item->original_name);
    }

    public function thumb(Request $request, string $code): StreamedResponse|Response
    {
        $item = Item::where('short_code', $code)->with('gallery')->firstOrFail();
        $this->authorizeAccess($item->gallery);

        $path = $item->thumb_path ?: $item->path;
        $mime = $item->thumb_path ? 'image/jpeg' : $item->mime;

        return $this->streamFromDisk($item->disk, $path, $mime);
    }

    private function authorizeAccess(Gallery $gallery): void
    {
        if ($gallery->isPublic()) {
            return;
        }

        if (! Auth::check()) {
            abort(redirect()->guest('/login'));
        }
    }

    private function streamFromDisk(string $disk, string $path, string $mime, ?string $downloadName = null): StreamedResponse|Response
    {
        $storage = Storage::disk($disk);
        if (! $storage->exists($path)) {
            abort(404);
        }

        $headers = [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=3600',
        ];

        return $storage->response($path, $downloadName, $headers, 'inline');
    }
}
