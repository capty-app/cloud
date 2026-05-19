<?php

namespace Database\Factories;

use App\Models\Gallery;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'gallery_id' => Gallery::factory(),
            'short_code' => Str::random(8),
            'disk' => 'local',
            'path' => 'galleries/test/'.Str::random(8).'.jpg',
            'thumb_path' => null,
            'original_name' => fake()->word().'.jpg',
            'mime' => 'image/jpeg',
            'size' => fake()->numberBetween(1024, 50_000),
            'kind' => 'image',
        ];
    }
}
