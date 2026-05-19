<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'item_id', 'gallery_id', 'viewer_user_id', 'ip_address', 'user_agent', 'referer',
    'country_code', 'country_name', 'region', 'city', 'latitude', 'longitude', 'geo_status',
])]
class ItemView extends Model
{
    use HasFactory;

    public const GEO_PENDING = 'pending';

    public const GEO_DONE = 'done';

    public const GEO_FAILED = 'failed';

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewer_user_id');
    }
}
