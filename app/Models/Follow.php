<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    //
    protected $connection = 'mysql';
    protected string $guard_name = 'api';
    //
    const FOLLOWING = 'following';
    const FOLLOWER = 'follower';
    //
    protected $fillable = [
        'user_id',
        'follower_user_id',
        'is_accept'
    ];
    //
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
            'is_accept' => 'boolean',
        ];
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function follower_user()
    {
        return $this->belongsTo(User::class, 'follower_user_id', 'id');
    }
    public function following_user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    //Scope
    public function scopeKeyword(Builder $query, string $value)
    {
        $value = '%' . $value . '%';
        return $query
            ->with('user')->orWhereHas('user', function (Builder $builder) use ($value) {
                $builder->where('name', 'like', $value)
                    ->orWhere('email', 'like', $value)
                    ->orWhere('telephone', 'like', $value);
            })->with('follower_user')->orWhereHas('follower_user', function (Builder $builder) use ($value) {
                $builder->where('name', 'like', $value)
                    ->orWhere('email', 'like', $value)
                    ->orWhere('telephone', 'like', $value);
            });
    }
}
