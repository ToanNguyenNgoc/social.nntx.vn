<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\LocalizesTimestamps;
use \Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens, InteractsWithMedia, LocalizesTimestamps;

    protected $connection = 'mysql';
    protected $guard_name = 'api';

    const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';
    const ROLE_ADMIN = 'ADMIN';

    const MAN = 1;
    const FEMALE = 2;

    const PLATFORM_REGISTER = 'client';
    const PLATFORM_REGISTER_GOOGLE = 'google';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telephone',
        'active',
        'birthday',
        'gender',
        'email_verified_at',
        'avatar_social_url',
        'platform'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'media',
        'avatar_social_url'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
            'deleted_at' => 'datetime:Y-m-d H:i:s',
            'birthday' => 'datetime:Y-m-d H:i:s',
            'active' => 'boolean',
        ];
    }
    protected $appends = [
        'avatar',
        'is_follow',
    ];
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection(MediaTemporary::COLLECTION_AVATAR)
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/png',
                'image/webp',
                'image/bmp',
                'image/gif',
            ])
            ->singleFile();
    }
    public function getAvatarAttribute(): null|string
    {
        $avatar = $this->getFirstMedia(MediaTemporary::COLLECTION_AVATAR)?->getFullUrl();
        if ($avatar) return $avatar;
        return $this->avatar_social_url;
    }

    public function followers()
    {
        return $this->hasMany(Follow::class, 'user_id', 'id');
    }

    public function getIsFollowAttribute(): bool
    {
        if (!auth('sanctum')->check()) return false;
        return $this->followers()->where('follower_user_id',  auth('sanctum')->user()->id)->exists();
    }

    public function getFollowerCountAttribute(): int
    {
        return $this->followers()->count();
    }

    //Scope
    public function scopeKeyword(Builder $query, string $value)
    {
        $value = '%' . $value . '%';
        return $query->where('name', 'like', $value)
            ->orWhere('telephone', 'like', $value)
            ->orWhere('email', 'like', $value);
    }
    //Stories
    public function stories(): HasMany
    {
        return $this->hasMany(Story::class, 'user_id', 'id');
    }
}
