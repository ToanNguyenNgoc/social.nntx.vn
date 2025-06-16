<?php

namespace App\Models;

use App\Traits\LocalizesTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Story extends Model implements HasMedia
{
    //
    use HasFactory, SoftDeletes, LocalizesTimestamps, InteractsWithMedia;
    //
    protected $connection = 'mysql';
    protected string $guard_name = 'api';

    protected $fillable = [
        'content',
        'user_id',
        'status',
        'expired_at',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
        'expired_at' => 'datetime:Y-m-d H:i:s',
        'status' => 'boolean',
    ];

    protected $hidden = [
        'expired_at',
        'media'
    ];

    protected $appends = [
        'media_url',
        'favorite_count',
        'is_favorite',
        'view_count',
        'is_view'
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(MediaTemporary::COLLECTION_STORY)
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/bmp',
                'image/gif',
            ])
            ->singleFile();
    }

    public function getMediaUrlAttribute(): null|string
    {
        $mediaUrl = $this->getFirstMedia(MediaTemporary::COLLECTION_STORY)?->getFullUrl();
        return $mediaUrl;
    }
    //Favorite
    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritetable')->with(['user']);
    }

    public function getFavoriteCountAttribute()
    {
        if (
            !auth('sanctum')->check()
            //|| $this->user_id !== auth('sanctum')->user()->id // Allow show for owner story
        )
            return null;
        return $this->favorites()->count();
    }

    public function getIsFavoriteAttribute(): bool
    {
        if (!auth('sanctum')->check()) return false;
        return $this->favorites()->where('user_id', auth('sanctum')->user()->id)->exists();
    }
    //View
    public function views(): HasMany
    {
        return $this->hasMany(StoryView::class, 'story_id', 'id')->with(['user']);
    }
    public function getViewCountAttribute()
    {
        if (
            !auth('sanctum')->check()
            //|| $this->user_id !== auth('sanctum')->user()->id // Allow show for owner story
        )
            return null;
        return $this->views()->count();
    }
    public function getIsViewAttribute(): bool
    {
        if (!auth('sanctum')->check()) return false;
        return $this->views()->where('user_id', auth('sanctum')->user()->id)->exists();
    }
    //Scope
    public function scopeValidExpired(Builder $query): Builder
    {
        return $query->where('expired_at', '>=', now());
    }
}
