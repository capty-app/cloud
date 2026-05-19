<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function destroy(Item $item): RedirectResponse
    {
        $disk = Storage::disk($item->disk);
        if ($disk->exists($item->path)) {
            $disk->delete($item->path);
        }
        if ($item->thumb_path && $disk->exists($item->thumb_path)) {
            $disk->delete($item->thumb_path);
        }

        $galleryId = $item->gallery_id;
        $item->delete();

        return redirect()
            ->route('admin.galleries.show', $galleryId)
            ->with('success', 'Item deleted.');
    }
}
