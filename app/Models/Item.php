<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'gallery_id', 'short_code', 'disk', 'path', 'thumb_path',
    'original_name', 'mime', 'size', 'kind',
])]
class Item extends Model
{
    use HasFactory;

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function views(): HasMany
    {
        return $this->hasMany(ItemView::class);
    }

    public static function generateShortCode(): string
    {
        do {
            $code = Str::random(8);
        } while (self::where('short_code', $code)->exists());

        return $code;
    }

    public function isImage(): bool
    {
        return $this->kind === 'image';
    }

    public function isVideo(): bool
    {
        return $this->kind === 'video';
    }

    public function fileUrl(): string
    {
        return route('item.file', $this->short_code);
    }

    public function thumbUrl(): ?string
    {
        if ($this->thumb_path) {
            return route('item.thumb', $this->short_code);
        }

        return $this->isImage() ? $this->fileUrl() : null;
    }

    public function viewerUrl(): string
    {
        return route('item.show', $this->short_code);
    }
}
