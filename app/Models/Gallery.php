<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'slug', 'description', 'visibility', 'api_token',
    'allowed_mimes', 'max_size_bytes', 'comments_enabled', 'created_by',
])]
class Gallery extends Model
{
    use HasFactory;

    public const VISIBILITY_PUBLIC = 'public';

    public const VISIBILITY_PRIVATE = 'private';

    protected function casts(): array
    {
        return [
            'allowed_mimes' => 'array',
            'comments_enabled' => 'boolean',
            'max_size_bytes' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPublic(): bool
    {
        return $this->visibility === self::VISIBILITY_PUBLIC;
    }

    public static function generateToken(): string
    {
        return 'gly_'.Str::random(40);
    }

    public function allowsMime(string $mime): bool
    {
        if (empty($this->allowed_mimes)) {
            return str_starts_with($mime, 'image/') || str_starts_with($mime, 'video/');
        }

        foreach ($this->allowed_mimes as $allowed) {
            if (str_ends_with($allowed, '/*')) {
                $prefix = substr($allowed, 0, -1);
                if (str_starts_with($mime, $prefix)) {
                    return true;
                }
            } elseif ($allowed === $mime) {
                return true;
            }
        }

        return false;
    }
}
