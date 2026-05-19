<?php

use App\Jobs\GeolocateItemView;
use App\Models\Gallery;
use App\Models\Item;
use App\Models\ItemView;
use Illuminate\Support\Facades\Http;

it('fills geo fields and clears the IP on a successful lookup', function () {
    Http::fake([
        'free.freeipapi.com/api/json/*' => Http::response([
            'countryCode' => 'NL',
            'countryName' => 'Netherlands',
            'regionName' => 'North Holland',
            'cityName' => 'Amsterdam',
            'latitude' => 52.3676,
            'longitude' => 4.9041,
        ], 200),
    ]);

    $gallery = Gallery::factory()->create();
    $item = Item::factory()->for($gallery)->create();
    $view = ItemView::create([
        'item_id' => $item->id,
        'gallery_id' => $gallery->id,
        'ip_address' => '203.0.113.10',
        'user_agent' => 'ua',
        'geo_status' => ItemView::GEO_PENDING,
    ]);

    (new GeolocateItemView($view->id))->handle();

    $view->refresh();
    expect($view->country_code)->toBe('NL');
    expect($view->country_name)->toBe('Netherlands');
    expect($view->city)->toBe('Amsterdam');
    expect((float) $view->latitude)->toBe(52.3676);
    expect($view->geo_status)->toBe(ItemView::GEO_DONE);
    expect($view->ip_address)->toBeNull();
});

it('marks the view failed when the API errors', function () {
    Http::fake([
        'free.freeipapi.com/api/json/*' => Http::response([], 500),
    ]);

    $gallery = Gallery::factory()->create();
    $item = Item::factory()->for($gallery)->create();
    $view = ItemView::create([
        'item_id' => $item->id,
        'gallery_id' => $gallery->id,
        'ip_address' => '203.0.113.10',
        'geo_status' => ItemView::GEO_PENDING,
    ]);

    (new GeolocateItemView($view->id))->handle();

    $view->refresh();
    expect($view->geo_status)->toBe(ItemView::GEO_FAILED);
    expect($view->country_code)->toBeNull();
    // IP is preserved on failure for potential retry.
    expect($view->ip_address)->toBe('203.0.113.10');
});

it('skips private/loopback IPs without calling the API', function () {
    Http::fake();

    $gallery = Gallery::factory()->create();
    $item = Item::factory()->for($gallery)->create();
    $view = ItemView::create([
        'item_id' => $item->id,
        'gallery_id' => $gallery->id,
        'ip_address' => '127.0.0.1',
        'geo_status' => ItemView::GEO_PENDING,
    ]);

    (new GeolocateItemView($view->id))->handle();

    Http::assertNothingSent();
    $view->refresh();
    expect($view->geo_status)->toBe(ItemView::GEO_DONE);
    expect($view->country_code)->toBeNull();
    expect($view->ip_address)->toBeNull();
});

it('does nothing for views that are no longer pending', function () {
    Http::fake();

    $gallery = Gallery::factory()->create();
    $item = Item::factory()->for($gallery)->create();
    $view = ItemView::create([
        'item_id' => $item->id,
        'gallery_id' => $gallery->id,
        'ip_address' => '203.0.113.10',
        'geo_status' => ItemView::GEO_DONE,
    ]);

    (new GeolocateItemView($view->id))->handle();

    Http::assertNothingSent();
    expect($view->fresh()->country_code)->toBeNull();
});

it('does nothing when the view no longer exists', function () {
    Http::fake();

    (new GeolocateItemView(999_999))->handle();

    Http::assertNothingSent();
});
