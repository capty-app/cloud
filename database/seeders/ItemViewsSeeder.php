<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemView;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ItemViewsSeeder extends Seeder
{
    private const LOCATIONS = [
        ['US', 'United States', 'California', 'San Francisco', 37.7749, -122.4194],
        ['US', 'United States', 'New York', 'New York', 40.7128, -74.0060],
        ['US', 'United States', 'Texas', 'Austin', 30.2672, -97.7431],
        ['GB', 'United Kingdom', 'England', 'London', 51.5074, -0.1278],
        ['DE', 'Germany', 'Berlin', 'Berlin', 52.5200, 13.4050],
        ['FR', 'France', 'Île-de-France', 'Paris', 48.8566, 2.3522],
        ['NL', 'Netherlands', 'North Holland', 'Amsterdam', 52.3676, 4.9041],
        ['CA', 'Canada', 'Ontario', 'Toronto', 43.6532, -79.3832],
        ['BR', 'Brazil', 'São Paulo', 'São Paulo', -23.5505, -46.6333],
        ['JP', 'Japan', 'Tokyo', 'Tokyo', 35.6762, 139.6503],
        ['SG', 'Singapore', 'Singapore', 'Singapore', 1.3521, 103.8198],
        ['AU', 'Australia', 'New South Wales', 'Sydney', -33.8688, 151.2093],
        ['IN', 'India', 'Maharashtra', 'Mumbai', 19.0760, 72.8777],
        ['IR', 'Iran', 'Tehran', 'Tehran', 35.6892, 51.3890],
        ['TR', 'Turkey', 'Istanbul', 'Istanbul', 41.0082, 28.9784],
        ['ES', 'Spain', 'Madrid', 'Madrid', 40.4168, -3.7038],
    ];

    private const USER_AGENTS = [
        // Chrome / macOS
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        // Chrome / Windows
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        // Safari / macOS
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15',
        // Safari / iOS
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/604.1',
        // Firefox / Linux
        'Mozilla/5.0 (X11; Linux x86_64; rv:125.0) Gecko/20100101 Firefox/125.0',
        // Firefox / Windows
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0',
        // Edge / Windows
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Edg/124.0.0.0',
        // Chrome / Android
        'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36',
    ];

    private const REFERERS = [
        null,
        null,
        null,
        'https://twitter.com/',
        'https://news.ycombinator.com/',
        'https://www.google.com/',
        'https://www.reddit.com/',
        'https://github.com/',
    ];

    public function run(): void
    {
        $items = Item::all();
        if ($items->isEmpty()) {
            $this->command?->warn('No items found. Upload at least one item first.');

            return;
        }

        $totalCreated = 0;

        foreach ($items as $item) {
            $viewCount = random_int(15, 80);

            for ($i = 0; $i < $viewCount; $i++) {
                $location = self::LOCATIONS[array_rand(self::LOCATIONS)];
                $ua = self::USER_AGENTS[array_rand(self::USER_AGENTS)];
                $referer = self::REFERERS[array_rand(self::REFERERS)];

                $daysAgo = $this->weightedRecentDay();
                $createdAt = Carbon::now()
                    ->subDays($daysAgo)
                    ->subMinutes(random_int(0, 1439));

                ItemView::create([
                    'item_id' => $item->id,
                    'gallery_id' => $item->gallery_id,
                    'viewer_user_id' => null,
                    'ip_address' => null,
                    'user_agent' => $ua,
                    'referer' => $referer,
                    'country_code' => $location[0],
                    'country_name' => $location[1],
                    'region' => $location[2],
                    'city' => $location[3],
                    'latitude' => $location[4],
                    'longitude' => $location[5],
                    'geo_status' => ItemView::GEO_DONE,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $totalCreated++;
            }
        }

        $this->command?->info("Seeded {$totalCreated} item views across {$items->count()} item(s).");
    }

    /**
     * Bias recent days more heavily, but keep some tail back to 30 days.
     */
    private function weightedRecentDay(): int
    {
        $r = mt_rand() / mt_getrandmax();

        if ($r < 0.4) {
            return random_int(0, 2);
        }
        if ($r < 0.7) {
            return random_int(3, 6);
        }
        if ($r < 0.9) {
            return random_int(7, 14);
        }

        return random_int(15, 29);
    }
}
