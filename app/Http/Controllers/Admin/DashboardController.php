<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\Item;
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

        return Inertia::render('admin/dashboard', [
            'stats' => [
                'galleries' => Gallery::count(),
                'items' => Item::count(),
                'users' => User::count(),
            ],
            'recentGalleries' => $recentGalleries,
            'recentItems' => $recentItems,
        ]);
    }
}
