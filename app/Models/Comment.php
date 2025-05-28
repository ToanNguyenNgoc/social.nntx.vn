<?php

namespace App\Models;

use App\Traits\LocalizesTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Comment extends Model implements HasMedia
{
    //
    use HasFactory, SoftDeletes, InteractsWithMedia, LocalizesTimestamps;
    protected $connection = 'mysql';
    protected string $guard_name = 'api';

    const COMMENTABLE_TYPE_REPLY = 'REPLY_COMMENT';
    const COMMENTABLE_TYPE_POST = 'POST';

    protected $fillable = [
        'user_id',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
            'deleted_at' => 'datetime:Y-m-d H:i:s',

        ];
    }

    protected $appends = [
        'media_urls',
        'favorite_count',
        'is_favorite'
    ];

    protected $hidden = ['media'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaTemporary::COLLECTION_COMMENT)->acceptsMimeTypes([
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/bmp',
            'image/gif',
            'video/webm',
            'video/mp4',
        ]);
    }

    function getMediaUrlsAttribute(): array
    {
        $urls = [];
        /** @var Media $m */
        foreach ($this->getMedia(MediaTemporary::COLLECTION_COMMENT) as $m) {
            $urls[] = $m->getFullUrl();
        }
        return $urls;
    }

    function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritetable')->with(['user']);
    }

    public function getFavoriteCountAttribute(): int
    {
        return $this->favorites()->count();
    }

    public function getIsFavoriteAttribute(): bool
    {
        if (!auth('sanctum')->check()) return false;
        return $this->favorites()->where('user_id', auth('sanctum')->user()->id)->exists();
    }
}
