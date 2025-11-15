<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'recipient',
        'type',
        'user_id',
        'data',
        'read_at'
    ];
}
