<?php

use App\Models\Gallery;
use App\Models\Item;

it('isImage / isVideo reflect kind', function () {
    expect((new Item(['kind' => 'image']))->isImage())->toBeTrue();
    expect((new Item(['kind' => 'video']))->isVideo())->toBeTrue();
    expect((new Item(['kind' => 'image']))->isVideo())->toBeFalse();
});

it('thumbUrl falls back to file URL for an image with no thumb_path', function () {
    $gallery = Gallery::factory()->create();
    $item = Item::factory()->for($gallery)->create(['kind' => 'image', 'thumb_path' => null]);

    expect($item->thumbUrl())->toBe($item->fileUrl());
});

it('thumbUrl is null for a video without a thumb', function () {
    $gallery = Gallery::factory()->create();
    $item = Item::factory()->for($gallery)->create(['kind' => 'video', 'thumb_path' => null]);

    expect($item->thumbUrl())->toBeNull();
});

it('thumbUrl uses the thumb route when thumb_path is set', function () {
    $gallery = Gallery::factory()->create();
    $item = Item::factory()->for($gallery)->create(['thumb_path' => 'galleries/x_thumb.jpg']);

    expect($item->thumbUrl())->toBe(route('item.thumb', $item->short_code));
});

it('generateShortCode produces an 8-char string', function () {
    expect(Item::generateShortCode())->toMatch('/^[A-Za-z0-9]{8}$/');
});
