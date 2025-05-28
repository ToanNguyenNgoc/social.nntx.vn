<?php

namespace App\Models;

use App\Traits\LocalizesTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model
{
    //
    use HasFactory, SoftDeletes, LocalizesTimestamps;
    //
    protected $connection = 'mysql';
    protected string $guard_name = 'api';

    const TOPIC_TYPE_DUOS = 'DUOS';
    const TOPIC_TYPE_GROUP = 'GROUP';

    protected $fillable = [
        'name',
        'type',
        'updated_at'
    ];

    protected $appends = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function topicUsers(): HasMany
    {
        return $this->hasMany(TopicUser::class, 'topic_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'topic_user', 'topic_id', 'user_id')->withPivot(['joined_at']);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function last_message()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
