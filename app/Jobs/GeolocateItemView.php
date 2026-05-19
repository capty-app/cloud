<?php

namespace App\Jobs;

use App\Models\ItemView;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeolocateItemView implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 15;

    public function __construct(public int $viewId) {}

    public function handle(): void
    {
        $view = ItemView::find($this->viewId);
        if (! $view || $view->geo_status !== ItemView::GEO_PENDING) {
            return;
        }

        $ip = $view->ip_address;
        if (! $ip || ! $this->isPublicIp($ip)) {
            $view->update([
                'geo_status' => ItemView::GEO_DONE,
                'ip_address' => null,
            ]);

            return;
        }

        try {
            $response = Http::timeout(5)
                ->acceptJson()
                ->get("https://free.freeipapi.com/api/json/{$ip}");
        } catch (Throwable $e) {
            Log::warning('freeipapi lookup failed', ['ip' => $ip, 'error' => $e->getMessage()]);
            $view->update(['geo_status' => ItemView::GEO_FAILED]);

            return;
        }

        if (! $response->successful()) {
            $view->update(['geo_status' => ItemView::GEO_FAILED]);

            return;
        }

        $data = $response->json() ?? [];

        $view->update([
            'country_code' => $data['countryCode'] ?? null,
            'country_name' => $data['countryName'] ?? null,
            'region' => $data['regionName'] ?? null,
            'city' => $data['cityName'] ?? null,
            'latitude' => isset($data['latitude']) ? (float) $data['latitude'] : null,
            'longitude' => isset($data['longitude']) ? (float) $data['longitude'] : null,
            'geo_status' => ItemView::GEO_DONE,
            'ip_address' => null,
        ]);
    }

    private function isPublicIp(string $ip): bool
    {
        return (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
