<?php

namespace App\Jobs;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class GenerateThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $itemId) {}

    public function handle(): void
    {
        $item = Item::find($this->itemId);
        if (! $item || ! $item->isImage()) {
            return;
        }

        $disk = Storage::disk($item->disk);
        if (! $disk->exists($item->path)) {
            return;
        }

        $manager = new ImageManager(new Driver);
        $image = $manager->decodeBinary($disk->get($item->path));
        $image->scaleDown(width: 600, height: 600);
        $encoded = (string) $image->encode(new JpegEncoder(quality: 80));

        $thumbPath = preg_replace('/\.[^.]+$/', '_thumb.jpg', $item->path);
        $disk->put($thumbPath, $encoded);

        $item->update(['thumb_path' => $thumbPath]);
    }
}
