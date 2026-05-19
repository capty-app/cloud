<?php

namespace Database\Factories;

use App\Models\Gallery;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Gallery>
 */
class GalleryFactory extends Factory
{
    protected $model = Gallery::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'description' => fake()->optional()->sentence(),
            'visibility' => Gallery::VISIBILITY_PRIVATE,
            'api_token' => Gallery::generateToken(),
            'allowed_mimes' => null,
            'max_size_bytes' => 100 * 1024 * 1024,
            'comments_enabled' => true,
        ];
    }
}
