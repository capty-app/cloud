<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\ItemController as AdminItemController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DocsController;
use App\Http\Controllers\GalleryViewController;
use App\Http\Controllers\SetupController;
use App\Models\Gallery;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }

    return redirect('/login');
});

Route::get('/setup', [SetupController::class, 'show'])->name('setup.show');
Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');

// Public/auth-gated gallery viewer
Route::get('/g/{slug}', [GalleryViewController::class, 'show'])->name('gallery.show');
Route::get('/s/{code}', [GalleryViewController::class, 'showItem'])->name('item.show');
Route::get('/f/{code}', [GalleryViewController::class, 'file'])->name('item.file');
Route::get('/t/{code}', [GalleryViewController::class, 'thumb'])->name('item.thumb');

Route::middleware('auth')->group(function () {
    Route::get('/docs', [DocsController::class, 'index'])->name('docs.index');
    Route::get('/docs/{page}', [DocsController::class, 'show'])
        ->where('page', '[a-zA-Z0-9\-]+')
        ->name('docs.show');

    Route::post('/s/{code}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('/dashboard', function () {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('user/dashboard', [
            'galleries' => Gallery::query()
                ->withCount('items')
                ->orderByDesc('updated_at')
                ->get()
                ->map(fn ($g) => [
                    'id' => $g->id,
                    'name' => $g->name,
                    'slug' => $g->slug,
                    'description' => $g->description,
                    'visibility' => $g->visibility,
                    'items_count' => $g->items_count,
                    'url' => route('gallery.show', $g->slug),
                ]),
        ]);
    })->name('dashboard');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('galleries', AdminGalleryController::class);
    Route::post('galleries/{gallery}/rotate-token', [AdminGalleryController::class, 'rotateToken'])
        ->name('galleries.rotate-token');

    Route::delete('items/{item}', [AdminItemController::class, 'destroy'])->name('items.destroy');

    Route::resource('users', AdminUserController::class)->except(['create', 'edit', 'show']);
});
