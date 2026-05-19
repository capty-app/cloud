<?php

use App\Jobs\GenerateThumbnail;
use App\Models\Gallery;
use App\Models\Item;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

it('writes a thumbnail and updates the item', function () {
    Storage::fake('local');
    $gallery = Gallery::factory()->create();
    $item = Item::factory()->for($gallery)->create([
        'disk' => 'local',
        'path' => 'galleries/1/x.png',
        'kind' => 'image',
        'thumb_path' => null,
    ]);

    // Create a real image file for the job to read.
    $manager = new ImageManager(new Driver);
    $img = $manager->createImage(40, 40);
    Storage::disk('local')->put($item->path, (string) $img->encode(new JpegEncoder));

    (new GenerateThumbnail($item->id))->handle();

    $item->refresh();
    expect($item->thumb_path)->toBe('galleries/1/x_thumb.jpg');
    Storage::disk('local')->assertExists($item->thumb_path);
});

it('skips non-image items', function () {
    Storage::fake('local');
    $item = Item::factory()->create(['kind' => 'video']);

    (new GenerateThumbnail($item->id))->handle();

    expect($item->fresh()->thumb_path)->toBeNull();
});

it('skips missing items', function () {
    (new GenerateThumbnail(99999))->handle();

    expect(true)->toBeTrue();
});
