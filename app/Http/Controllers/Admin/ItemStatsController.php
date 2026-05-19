<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemView;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ItemStatsController extends Controller
{
    public function __invoke(Request $request, Item $item): Response
    {
        $item->load('gallery');

        $total = ItemView::where('item_id', $item->id)->count();
        $last7d = ItemView::where('item_id', $item->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $timeline = $this->dailyTimeline($item->id, 30);
        $topCountries = $this->topCountries($item->id);
        $topUserAgents = $this->topUserAgents($item->id);
        $recent = $this->recentViews($item->id);

        return Inertia::render('admin/items/stats', [
            'item' => [
                'id' => $item->id,
                'short_code' => $item->short_code,
                'kind' => $item->kind,
                'mime' => $item->mime,
                'thumb_url' => $item->thumbUrl(),
                'viewer_url' => $item->viewerUrl(),
                'original_name' => $item->original_name,
            ],
            'gallery' => [
                'id' => $item->gallery->id,
                'name' => $item->gallery->name,
                'slug' => $item->gallery->slug,
            ],
            'totals' => [
                'all_time' => $total,
                'last_7d' => $last7d,
            ],
            'timeline' => $timeline,
            'top_countries' => $topCountries,
            'top_user_agents' => $topUserAgents,
            'recent_views' => $recent,
        ]);
    }

    private function dailyTimeline(int $itemId, int $days): array
    {
        $since = now()->subDays($days - 1)->startOfDay();

        $rows = ItemView::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->where('item_id', $itemId)
            ->where('created_at', '>=', $since)
            ->groupBy('day')
            ->pluck('count', 'day');

        $out = [];
        for ($i = 0; $i < $days; $i++) {
            $day = $since->copy()->addDays($i)->toDateString();
            $out[] = [
                'date' => $day,
                'count' => (int) ($rows[$day] ?? 0),
            ];
        }

        return $out;
    }

    private function topCountries(int $itemId): array
    {
        return ItemView::query()
            ->selectRaw('country_code, country_name, COUNT(*) as count')
            ->where('item_id', $itemId)
            ->whereNotNull('country_code')
            ->groupBy('country_code', 'country_name')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'country_code' => $r->country_code,
                'country_name' => $r->country_name,
                'count' => (int) $r->count,
            ])
            ->all();
    }

    private function topUserAgents(int $itemId): array
    {
        $rows = ItemView::query()
            ->select('user_agent')
            ->where('item_id', $itemId)
            ->whereNotNull('user_agent')
            ->get();

        $buckets = [];
        foreach ($rows as $row) {
            $label = $this->summarizeUserAgent($row->user_agent);
            $buckets[$label] = ($buckets[$label] ?? 0) + 1;
        }
        arsort($buckets);

        $out = [];
        foreach (array_slice($buckets, 0, 10, true) as $label => $count) {
            $out[] = ['label' => $label, 'count' => $count];
        }

        return $out;
    }

    private function recentViews(int $itemId): array
    {
        $paginator = ItemView::query()
            ->where('item_id', $itemId)
            ->latest()
            ->simplePaginate(25)
            ->withQueryString();

        return [
            'data' => collect($paginator->items())->map(fn (ItemView $v) => [
                'id' => $v->id,
                'created_at' => $v->created_at?->toIso8601String(),
                'country_code' => $v->country_code,
                'country_name' => $v->country_name,
                'city' => $v->city,
                'region' => $v->region,
                'user_agent_summary' => $this->summarizeUserAgent($v->user_agent),
                'user_agent' => $v->user_agent,
                'referer' => $v->referer,
            ])->all(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'next_page_url' => $paginator->nextPageUrl(),
            'current_page' => $paginator->currentPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }

    private function summarizeUserAgent(?string $ua): string
    {
        if (! $ua) {
            return 'Unknown';
        }

        $ua = trim($ua);

        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Safari/') => 'Safari',
            str_contains($ua, 'curl/') => 'curl',
            str_contains($ua, 'wget') => 'wget',
            str_contains($ua, 'bot') || str_contains($ua, 'Bot') => 'Bot',
            default => 'Other',
        };

        $os = match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS X') || str_contains($ua, 'Macintosh') => 'macOS',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Unknown OS',
        };

        return "{$browser} · {$os}";
    }
}
