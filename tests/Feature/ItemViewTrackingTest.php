<?php

use App\Jobs\GeolocateItemView;
use App\Models\Gallery;
use App\Models\Item;
use App\Models\ItemView;
use App\Models\User;
use Illuminate\Support\Facades\Bus;

beforeEach(function () {
    User::factory()->admin()->create();
});

it('records a view when an anonymous visitor loads /s/{code}', function () {
    Bus::fake();
    $gallery = Gallery::factory()->create(['visibility' => 'public']);
    $item = Item::factory()->for($gallery)->create();

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.45'])
        ->withHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) Chrome/124.0')
        ->withHeader('Referer', 'https://example.com/page')
        ->get("/s/{$item->short_code}")
        ->assertOk();

    expect(ItemView::count())->toBe(1);

    $view = ItemView::first();
    expect($view->item_id)->toBe($item->id);
    expect($view->gallery_id)->toBe($gallery->id);
    expect($view->viewer_user_id)->toBeNull();
    expect($view->ip_address)->toBe('203.0.113.45');
    expect($view->user_agent)->toContain('Chrome/124.0');
    expect($view->referer)->toBe('https://example.com/page');
    expect($view->geo_status)->toBe(ItemView::GEO_PENDING);

    Bus::assertDispatched(GeolocateItemView::class, fn ($job) => $job->viewId === $view->id);
});

it('records a view for a signed-in non-admin user', function () {
    Bus::fake();
    $gallery = Gallery::factory()->create(['visibility' => 'public']);
    $item = Item::factory()->for($gallery)->create();
    $user = User::factory()->create();

    $this->actingAs($user)->get("/s/{$item->short_code}")->assertOk();

    expect(ItemView::count())->toBe(1);
    expect(ItemView::first()->viewer_user_id)->toBe($user->id);
    Bus::assertDispatched(GeolocateItemView::class);
});

it('does NOT record a view when an admin loads the page', function () {
    Bus::fake();
    $gallery = Gallery::factory()->create(['visibility' => 'public']);
    $item = Item::factory()->for($gallery)->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get("/s/{$item->short_code}")->assertOk();

    expect(ItemView::count())->toBe(0);
    Bus::assertNotDispatched(GeolocateItemView::class);
});
