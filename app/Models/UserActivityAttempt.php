<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'activity_type',
        'attempts',
        'max_attempts',
        'penalty_action'
    ];
}
