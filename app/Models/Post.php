<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use \Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Post extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;
    //
    protected $connection = 'mysql';
    protected string $guard_name = 'api';

    protected $fillable = [
        'user_id',
        'content',
        'status'
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
            'deleted_at' => 'datetime:Y-m-d H:i:s',
            'status' => 'boolean',
        ];
    }

    protected $appends = [
        'media_urls',
        'comment_count',
        'favorite_count',
        'is_favorite'
    ];

    protected $hidden = ['media'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaTemporary::COLLECTION_POST)->acceptsMimeTypes([
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
        foreach ($this->getMedia(MediaTemporary::COLLECTION_POST) as $m) {
            $urls[] = $m->getFullUrl();
        }
        return $urls;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scopeKeyword(Builder $query, string $value)
    {
        $value = '%' . $value . '%';
        return $query->where('content', 'like', $value);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function getCommentCountAttribute(): int
    {
        return $this->comments()->count();
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
