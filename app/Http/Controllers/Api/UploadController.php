<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateThumbnail;
use App\Models\Gallery;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function __invoke(Request $request, string $slug): JsonResponse
    {
        $gallery = Gallery::where('slug', $slug)->first();
        if (! $gallery) {
            return response()->json(['error' => 'Gallery not found.'], 404);
        }

        $token = $this->extractToken($request);
        if (! $token || ! hash_equals($gallery->api_token, $token)) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }

        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $file = $request->file('file');
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $size = $file->getSize();

        if ($size > $gallery->max_size_bytes) {
            return response()->json([
                'error' => 'File exceeds max size.',
                'max_size_bytes' => $gallery->max_size_bytes,
            ], 422);
        }

        if (! $gallery->allowsMime($mime)) {
            return response()->json([
                'error' => 'File type not allowed for this gallery.',
                'mime' => $mime,
            ], 422);
        }

        $kind = str_starts_with($mime, 'video/') ? 'video' : 'image';
        $disk = config('filesystems.default');
        $shortCode = Item::generateShortCode();
        $ext = $file->getClientOriginalExtension() ?: $file->extension();
        $path = "galleries/{$gallery->id}/{$shortCode}.{$ext}";

        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

        $item = Item::create([
            'gallery_id' => $gallery->id,
            'short_code' => $shortCode,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $mime,
            'size' => $size,
            'kind' => $kind,
        ]);

        if ($kind === 'image') {
            GenerateThumbnail::dispatch($item->id);
        }

        return response()->json([
            'id' => $item->id,
            'short_code' => $item->short_code,
            'url' => $item->viewerUrl(),         // public short URL to view this item
            'short_url' => $item->viewerUrl(),   // alias
            'file_url' => $item->fileUrl(),      // direct file URL
            'thumb_url' => $item->thumbUrl(),    // thumbnail URL (may be original for non-image)
            'gallery_url' => route('gallery.show', $gallery->slug),
            'mime' => $item->mime,
            'size' => $item->size,
            'kind' => $item->kind,
            'original_name' => $item->original_name,
        ], 201);
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization');
        if ($header && preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
            return trim($m[1]);
        }

        return $request->header('X-Api-Token');
    }
}
