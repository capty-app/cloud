<?php

use App\Models\Comment;
use App\Models\Gallery;
use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    User::factory()->admin()->create();
    Storage::fake('local');
});

it('lets anonymous visitors view public galleries', function () {
    $gallery = Gallery::factory()->create(['visibility' => 'public']);

    $this->get("/g/{$gallery->slug}")->assertOk();
});

it('redirects anonymous visitors away from private galleries', function () {
    $gallery = Gallery::factory()->create(['visibility' => 'private']);

    $this->get("/g/{$gallery->slug}")->assertRedirect('/login');
});

it('lets signed-in users view private galleries', function () {
    $gallery = Gallery::factory()->create(['visibility' => 'private']);
    $user = User::factory()->create();

    $this->actingAs($user)->get("/g/{$gallery->slug}")->assertOk();
});

it('returns 404 for unknown gallery slugs', function () {
    $this->get('/g/does-not-exist')->assertNotFound();
});

it('streams an item file', function () {
    $gallery = Gallery::factory()->create(['visibility' => 'public']);
    $item = Item::factory()->for($gallery)->create(['path' => 'galleries/1/x.jpg']);
    Storage::disk('local')->put($item->path, 'file-bytes');

    $resp = $this->get("/f/{$item->short_code}");
    $resp->assertOk();
    expect($resp->headers->get('Content-Type'))->toContain($item->mime);
});

it('serves a thumbnail or falls back to the original file', function () {
    $gallery = Gallery::factory()->create(['visibility' => 'public']);
    $item = Item::factory()->for($gallery)->create(['path' => 'galleries/1/x.jpg', 'thumb_path' => null]);
    Storage::disk('local')->put($item->path, 'orig-bytes');

    $this->get("/t/{$item->short_code}")->assertOk();
});

it('renders the per-item viewer page', function () {
    $gallery = Gallery::factory()->create(['visibility' => 'public']);
    $item = Item::factory()->for($gallery)->create();

    $this->get("/s/{$item->short_code}")->assertOk();
});

it('lets signed-in users post a comment when enabled', function () {
    $gallery = Gallery::factory()->create(['comments_enabled' => true, 'visibility' => 'public']);
    $item = Item::factory()->for($gallery)->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/s/{$item->short_code}/comments", ['body' => 'hello'])
        ->assertRedirect();

    expect(Comment::count())->toBe(1);
    expect(Comment::first()->user_id)->toBe($user->id);
});

it('refuses to post comments when gallery has comments disabled', function () {
    $gallery = Gallery::factory()->create(['comments_enabled' => false, 'visibility' => 'public']);
    $item = Item::factory()->for($gallery)->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post("/s/{$item->short_code}/comments", ['body' => 'hello'])
        ->assertForbidden();
});

it('blocks anonymous comment posts', function () {
    $gallery = Gallery::factory()->create(['comments_enabled' => true, 'visibility' => 'public']);
    $item = Item::factory()->for($gallery)->create();

    $this->post("/s/{$item->short_code}/comments", ['body' => 'hello'])
        ->assertRedirect('/login');
});

it('lets the owner delete their own comment', function () {
    $gallery = Gallery::factory()->create(['comments_enabled' => true, 'visibility' => 'public']);
    $item = Item::factory()->for($gallery)->create();
    $user = User::factory()->create();
    $comment = Comment::factory()->for($item)->for($user)->create();

    $this->actingAs($user)
        ->delete("/comments/{$comment->id}")
        ->assertRedirect();

    expect(Comment::count())->toBe(0);
});

it('lets admins delete any comment', function () {
    $gallery = Gallery::factory()->create(['comments_enabled' => true, 'visibility' => 'public']);
    $item = Item::factory()->for($gallery)->create();
    $author = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $comment = Comment::factory()->for($item)->for($author)->create();

    $this->actingAs($admin)
        ->delete("/comments/{$comment->id}")
        ->assertRedirect();

    expect(Comment::count())->toBe(0);
});

it("blocks deleting another user's comment", function () {
    $gallery = Gallery::factory()->create(['comments_enabled' => true, 'visibility' => 'public']);
    $item = Item::factory()->for($gallery)->create();
    $author = User::factory()->create();
    $stranger = User::factory()->create();
    $comment = Comment::factory()->for($item)->for($author)->create();

    $this->actingAs($stranger)
        ->delete("/comments/{$comment->id}")
        ->assertForbidden();
});
