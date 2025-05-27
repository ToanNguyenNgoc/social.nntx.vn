<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationLog extends Model
{
    //
    use HasFactory, SoftDeletes;
    //
    protected $connection = 'mysql';
    protected string $guard_name = 'api';

    protected $fillable = [
        'message',
        'type_id',
        'payload_id',
        'sender_id',
        'received_id',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }
}
