<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRead extends Model
{
    protected $table = 'notification_reads';

    protected $fillable = ['user_id', 'app_notification_id', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public $timestamps = true;
}
