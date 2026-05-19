<?php

use App\Models\Gallery;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('creates a gallery with a generated slug and token', function () {
    $this->actingAs($this->admin)
        ->post('/admin/galleries', [
            'name' => 'My Trip',
            'slug' => '',
            'description' => 'Holiday photos',
            'visibility' => 'public',
            'max_size_mb' => 25,
            'allowed_mimes_raw' => 'image/*',
            'comments_enabled' => '1',
        ])
        ->assertRedirect();

    $gallery = Gallery::first();
    expect($gallery)->not->toBeNull();
    expect($gallery->slug)->toBe('my-trip');
    expect($gallery->visibility)->toBe('public');
    expect($gallery->max_size_bytes)->toBe(25 * 1024 * 1024);
    expect($gallery->allowed_mimes)->toBe(['image/*']);
    expect($gallery->comments_enabled)->toBeTrue();
    expect($gallery->api_token)->toStartWith('gly_');
    expect($gallery->created_by)->toBe($this->admin->id);
});

it('deduplicates slugs', function () {
    Gallery::factory()->create(['slug' => 'photos']);

    $this->actingAs($this->admin)
        ->post('/admin/galleries', [
            'name' => 'Photos',
            'slug' => 'photos',
            'visibility' => 'private',
            'max_size_mb' => 100,
            'comments_enabled' => '0',
        ]);

    expect(Gallery::pluck('slug')->all())->toEqualCanonicalizing(['photos', 'photos-2']);
});

it('updates a gallery', function () {
    $gallery = Gallery::factory()->create(['visibility' => 'private']);

    $this->actingAs($this->admin)
        ->put("/admin/galleries/{$gallery->id}", [
            'name' => $gallery->name,
            'slug' => $gallery->slug,
            'description' => 'Updated',
            'visibility' => 'public',
            'max_size_mb' => 200,
            'comments_enabled' => '0',
        ])
        ->assertRedirect();

    $gallery->refresh();
    expect($gallery->visibility)->toBe('public');
    expect($gallery->comments_enabled)->toBeFalse();
    expect($gallery->max_size_bytes)->toBe(200 * 1024 * 1024);
});

it('rotates the api token', function () {
    $gallery = Gallery::factory()->create();
    $original = $gallery->api_token;

    $this->actingAs($this->admin)
        ->post("/admin/galleries/{$gallery->id}/rotate-token")
        ->assertRedirect();

    expect($gallery->fresh()->api_token)->not->toBe($original);
});

it('deletes a gallery (and cascades to items)', function () {
    $gallery = Gallery::factory()->create();
    Item::factory()->for($gallery)->create();

    $this->actingAs($this->admin)
        ->delete("/admin/galleries/{$gallery->id}")
        ->assertRedirect('/admin/galleries');

    expect(Gallery::count())->toBe(0);
    expect(Item::count())->toBe(0);
});

it('validates required fields on create', function () {
    $this->actingAs($this->admin)
        ->post('/admin/galleries', [
            'name' => '',
            'visibility' => 'invalid',
            'max_size_mb' => 0,
        ])
        ->assertSessionHasErrors(['name', 'visibility', 'max_size_mb']);
});

it('blocks non-admin users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/galleries', ['name' => 'Nope'])
        ->assertForbidden();
});

it('renders the gallery index page', function () {
    Gallery::factory()->count(2)->create();

    $this->actingAs($this->admin)
        ->get('/admin/galleries')
        ->assertOk();
});

it('renders the new-gallery page', function () {
    $this->actingAs($this->admin)
        ->get('/admin/galleries/create')
        ->assertOk();
});

it('renders the gallery show page with token, items, and endpoint', function () {
    $gallery = Gallery::factory()->create();
    Item::factory()->for($gallery)->count(2)->create();

    $this->actingAs($this->admin)
        ->get("/admin/galleries/{$gallery->id}")
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('admin/galleries/show')
                ->where('gallery.api_token', $gallery->api_token)
                ->where('upload_endpoint', url("/api/galleries/{$gallery->slug}/upload"))
                ->has('items', 2)
        );
});

it('renders the edit page', function () {
    $gallery = Gallery::factory()->create();

    $this->actingAs($this->admin)
        ->get("/admin/galleries/{$gallery->id}/edit")
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page->component('admin/galleries/edit')
                ->where('gallery.id', $gallery->id)
        );
});

it('updates the slug uniquely when changed to a colliding value', function () {
    $a = Gallery::factory()->create(['slug' => 'one']);
    $b = Gallery::factory()->create(['slug' => 'two']);

    $this->actingAs($this->admin)
        ->put("/admin/galleries/{$b->id}", [
            'name' => $b->name,
            'slug' => 'one',
            'visibility' => $b->visibility,
            'max_size_mb' => 10,
            'comments_enabled' => '0',
        ])->assertRedirect();

    $b->refresh();
    expect($b->slug)->toBe('one-2');
    expect($a->fresh()->slug)->toBe('one');
});

it('deletes individual items and removes files', function () {
    Storage::fake('local');
    $gallery = Gallery::factory()->create();
    $item = Item::factory()->for($gallery)->create([
        'path' => 'galleries/1/abc.png',
    ]);
    Storage::disk('local')->put($item->path, 'fake-bytes');

    $this->actingAs($this->admin)
        ->delete("/admin/items/{$item->id}")
        ->assertRedirect();

    expect(Item::count())->toBe(0);
    Storage::disk('local')->assertMissing('galleries/1/abc.png');
});
