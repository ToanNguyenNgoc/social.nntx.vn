<?php

namespace App\Models;

use App\Traits\LocalizesTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    //
    use HasFactory, LocalizesTimestamps;
    protected $connection = 'mysql';
    protected string $guard_name = 'api';

    const TYPE_POST = 'POST';
    const TYPE_COMMENT = 'COMMENT';
    const TYPE_MESSAGE = 'MESSAGE';

    protected $fillable = [
        'user_id',
        'favoritetable_id',
        'favoritetable_type'
    ];

    function favoritetable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
