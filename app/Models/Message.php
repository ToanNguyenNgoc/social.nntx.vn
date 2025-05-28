<?php

namespace App\Models;

use App\Traits\LocalizesTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Message extends Model implements HasMedia
{
    //
    use HasFactory, SoftDeletes, InteractsWithMedia, LocalizesTimestamps;
    protected $connection = 'mysql';
    protected string $guard_name = 'api';

    protected $hidden = [
        'media'
    ];

    protected $fillable = [
        'body',
        'user_id',
        'topic_id',
        'reply_id'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = [
        'media_urls',
        'user',
        'favorite_count',
        'is_favorite'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaTemporary::COLLECTION_MESSAGE)->acceptsMimeTypes([
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
        foreach ($this->getMedia(MediaTemporary::COLLECTION_MESSAGE) as $m) {
            $urls[] = $m->getFullUrl();
        }
        return $urls;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getUserAttribute()
    {
        return $this->user()->first();
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

    public function reply()
    {
        return $this->belongsTo(Message::class, 'reply_id', 'id');
    }
}
