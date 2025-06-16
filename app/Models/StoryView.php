<?php

namespace App\Models;

use App\Traits\LocalizesTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoryView extends Model
{
    //
    use HasFactory, LocalizesTimestamps;
    //
    protected $connection = 'mysql';
    protected string $guard_name = 'api';

    protected $fillable = [
        'user_id',
        'story_id'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
