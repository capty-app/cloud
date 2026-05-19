<?php

use App\Models\Gallery;
use App\Models\Item;
use App\Models\ItemView;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

it('blocks non-admins from the stats page', function () {
    $gallery = Gallery::factory()->create();
    $item = Item::factory()->for($gallery)->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get("/admin/items/{$item->id}/stats")
        ->assertForbidden();
});

it('redirects guests to login', function () {
    $gallery = Gallery::factory()->create();
    $item = Item::factory()->for($gallery)->create();

    $this->get("/admin/items/{$item->id}/stats")->assertRedirect('/login');
});

it('renders totals, top countries, and paginated recent views', function () {
    $gallery = Gallery::factory()->create();
    $item = Item::factory()->for($gallery)->create();

    // 3 from US, 1 from DE, all "done" geo
    ItemView::factory()->count(3)->state([
        'item_id' => $item->id,
        'gallery_id' => $gallery->id,
        'country_code' => 'US',
        'country_name' => 'United States',
        'geo_status' => ItemView::GEO_DONE,
    ])->create();
    ItemView::factory()->state([
        'item_id' => $item->id,
        'gallery_id' => $gallery->id,
        'country_code' => 'DE',
        'country_name' => 'Germany',
        'geo_status' => ItemView::GEO_DONE,
    ])->create();

    // 30 more US views to force pagination (page size 25).
    ItemView::factory()->count(30)->state([
        'item_id' => $item->id,
        'gallery_id' => $gallery->id,
        'country_code' => 'US',
        'country_name' => 'United States',
        'geo_status' => ItemView::GEO_DONE,
    ])->create();

    $this->actingAs($this->admin)
        ->get("/admin/items/{$item->id}/stats")
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('admin/items/stats')
                ->where('totals.all_time', 34)
                ->where('top_countries.0.country_code', 'US')
                ->where('top_countries.0.count', 33)
                ->where('recent_views.current_page', 1)
                ->has('recent_views.data', 25)
                ->where('recent_views.next_page_url', fn ($url) => str_contains((string) $url, 'page=2'))
                ->where('recent_views.prev_page_url', null)
        );
});

it('returns the second page of recent views', function () {
    $gallery = Gallery::factory()->create();
    $item = Item::factory()->for($gallery)->create();
    ItemView::factory()->count(30)->state([
        'item_id' => $item->id,
        'gallery_id' => $gallery->id,
        'geo_status' => ItemView::GEO_DONE,
    ])->create();

    $this->actingAs($this->admin)
        ->get("/admin/items/{$item->id}/stats?page=2")
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->where('recent_views.current_page', 2)
                ->has('recent_views.data', 5)
                ->where('recent_views.next_page_url', null)
                ->where('recent_views.prev_page_url', fn ($url) => str_contains((string) $url, 'page=1'))
        );
});

it('exposes view counts on the admin dashboard', function () {
    $gallery = Gallery::factory()->create();
    $item = Item::factory()->for($gallery)->create();
    ItemView::factory()->count(2)->state([
        'item_id' => $item->id,
        'gallery_id' => $gallery->id,
        'created_at' => now(),
    ])->create();
    ItemView::factory()->state([
        'item_id' => $item->id,
        'gallery_id' => $gallery->id,
        'created_at' => now()->subDays(20),
    ])->create();

    $this->actingAs($this->admin)
        ->get('/admin')
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->where('stats.views_total', 3)
                ->where('stats.views_last_7d', 2)
                ->where('topViewedItems.0.id', $item->id)
                ->where('topViewedItems.0.views_count', 3)
        );
});
