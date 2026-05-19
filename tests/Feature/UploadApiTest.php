<?php

use App\Jobs\GenerateThumbnail;
use App\Models\Gallery;
use App\Models\Item;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    Queue::fake();
});

it('rejects requests without an Authorization header', function () {
    $gallery = Gallery::factory()->create();

    $this->postJson("/api/galleries/{$gallery->slug}/upload", [
        'file' => UploadedFile::fake()->image('a.jpg'),
    ])->assertStatus(401);
});

it('rejects requests with a wrong bearer token', function () {
    $gallery = Gallery::factory()->create();

    $this->postJson(
        "/api/galleries/{$gallery->slug}/upload",
        ['file' => UploadedFile::fake()->image('a.jpg')],
        ['Authorization' => 'Bearer wrong']
    )->assertStatus(401);
});

it('returns 404 for an unknown slug', function () {
    $this->postJson('/api/galleries/no-such-gallery/upload', [
        'file' => UploadedFile::fake()->image('a.jpg'),
    ], ['Authorization' => 'Bearer whatever'])->assertStatus(404);
});

it('rejects files that exceed max_size_bytes', function () {
    $gallery = Gallery::factory()->create(['max_size_bytes' => 10]);

    $this->postJson(
        "/api/galleries/{$gallery->slug}/upload",
        ['file' => UploadedFile::fake()->create('big.jpg', 500, 'image/jpeg')],
        ['Authorization' => 'Bearer '.$gallery->api_token]
    )->assertStatus(422)->assertJsonPath('error', 'File exceeds max size.');
});

it('rejects mimes outside allowed_mimes', function () {
    $gallery = Gallery::factory()->create(['allowed_mimes' => ['video/mp4']]);

    $this->postJson(
        "/api/galleries/{$gallery->slug}/upload",
        ['file' => UploadedFile::fake()->image('a.jpg')],
        ['Authorization' => 'Bearer '.$gallery->api_token]
    )->assertStatus(422)->assertJsonPath('error', 'File type not allowed for this gallery.');
});

it('accepts an image and returns the short URL payload', function () {
    $gallery = Gallery::factory()->create();

    $resp = $this->postJson(
        "/api/galleries/{$gallery->slug}/upload",
        ['file' => UploadedFile::fake()->image('photo.jpg')],
        ['Authorization' => 'Bearer '.$gallery->api_token]
    );

    $resp->assertStatus(201)
        ->assertJsonStructure([
            'id', 'short_code', 'url', 'short_url', 'file_url',
            'thumb_url', 'gallery_url', 'mime', 'size', 'kind', 'original_name',
        ])
        ->assertJsonPath('kind', 'image')
        ->assertJsonPath('original_name', 'photo.jpg');

    $item = Item::first();
    expect($item)->not->toBeNull();
    expect($item->gallery_id)->toBe($gallery->id);
    Storage::disk('local')->assertExists($item->path);
    Queue::assertPushed(GenerateThumbnail::class);
});

it('classifies videos as kind=video and does not queue a thumbnail', function () {
    $gallery = Gallery::factory()->create();

    $resp = $this->postJson(
        "/api/galleries/{$gallery->slug}/upload",
        ['file' => UploadedFile::fake()->create('movie.mp4', 50, 'video/mp4')],
        ['Authorization' => 'Bearer '.$gallery->api_token]
    );

    $resp->assertStatus(201)->assertJsonPath('kind', 'video');
    Queue::assertNotPushed(GenerateThumbnail::class);
});

it('honors the X-Api-Token header as a fallback', function () {
    $gallery = Gallery::factory()->create();

    $this->postJson(
        "/api/galleries/{$gallery->slug}/upload",
        ['file' => UploadedFile::fake()->image('a.jpg')],
        ['X-Api-Token' => $gallery->api_token]
    )->assertStatus(201);
});
