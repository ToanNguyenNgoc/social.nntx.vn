<?php

namespace App\Models;

use App\Traits\LocalizesTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    //
    use HasFactory, LocalizesTimestamps;
    //
    protected $connection = 'mysql';
    protected $guard_name = 'api';

    protected $fillable = [
        'email',
        'otp',
        'expired_at'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
        'expired_at' => 'datetime:Y-m-d H:i:s',
    ];
}
