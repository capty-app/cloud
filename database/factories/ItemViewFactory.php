<?php

namespace Database\Factories;

use App\Models\Gallery;
use App\Models\Item;
use App\Models\ItemView;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemView>
 */
class ItemViewFactory extends Factory
{
    protected $model = ItemView::class;

    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'gallery_id' => Gallery::factory(),
            'viewer_user_id' => null,
            'ip_address' => null,
            'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) Chrome/124.0',
            'referer' => null,
            'country_code' => 'US',
            'country_name' => 'United States',
            'region' => 'California',
            'city' => 'San Francisco',
            'latitude' => 37.7749,
            'longitude' => -122.4194,
            'geo_status' => ItemView::GEO_DONE,
        ];
    }
}
