<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopicUser extends Model
{
    //
    use HasFactory;
    //
    protected $connection = 'mysql';
    protected string $guard_name = 'api';
    protected $table = 'topic_user';

    protected $fillable = [
        'topic_id',
        'user_id',
        'joined_at'
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'joined_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
