<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    //
    use HasFactory;
    protected $connection = 'mysql';
    protected string $guard_name = 'api';

    function favoritetable(): MorphTo
    {
        return $this->morphTo();
    }
}
