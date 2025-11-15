<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = [
        'recipient',
        'channel',
        'action_type',
        'token',
        'attempts',
        'user_id',
        'status',
        'expires_at'
    ];


    protected $hidden = [
        'token'
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime'
        ];
    }
}
