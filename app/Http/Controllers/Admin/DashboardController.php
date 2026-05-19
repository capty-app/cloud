<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Item;
use App\Models\ItemView;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $recentItems = Item::with('gallery')->latest()->take(8)->get()->map(fn (Item $i) => [
            'id' => $i->id,
            'short_code' => $i->short_code,
            'kind' => $i->kind,
            'thumb_url' => $i->thumbUrl(),
            'viewer_url' => $i->viewerUrl(),
            'gallery_name' => $i->gallery?->name,
            'gallery_slug' => $i->gallery?->slug,
        ]);

        $recentGalleries = Gallery::latest()->take(5)->get()->map(fn (Gallery $g) => [
            'id' => $g->id,
            'name' => $g->name,
            'slug' => $g->slug,
            'visibility' => $g->visibility,
            'created_at' => $g->created_at?->toIso8601String(),
        ]);

        $topViewedItems = Item::with('gallery')
            ->withCount('views')
            ->orderByDesc('views_count')
            ->take(8)
            ->get()
            ->filter(fn (Item $i) => $i->views_count > 0)
            ->values()
            ->map(fn (Item $i) => [
                'id' => $i->id,
                'short_code' => $i->short_code,
                'kind' => $i->kind,
                'thumb_url' => $i->thumbUrl(),
                'stats_url' => route('admin.items.stats', $i->id),
                'gallery_name' => $i->gallery?->name,
                'gallery_id' => $i->gallery?->id,
                'views_count' => (int) $i->views_count,
            ]);

        return Inertia::render('admin/dashboard', [
            'stats' => [
                'galleries' => Gallery::count(),
                'items' => Item::count(),
                'users' => User::count(),
                'views_total' => ItemView::count(),
                'views_last_7d' => ItemView::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'recentGalleries' => $recentGalleries,
            'recentItems' => $recentItems,
            'topViewedItems' => $topViewedItems,
        ]);
    }
}
