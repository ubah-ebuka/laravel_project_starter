<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'status',
        'type',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
        'currency_code',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed'
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function userPermissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function computePermissions(): Collection
    {
        return self::fetchPermissions($this);
    }

    public function attachPermissions()
    {
        $this->setRelation('permissions', $this->computePermissions());
        return $this;
    }

    public static function fetchPermissions(User $user)
    {
        $roleCacheKey = "compute_role_permissions_{$user->role_id}";
        $userCacheKey = "compute_user_permissions_{$user->id}";
    
        $permissionsByRole = cache()->rememberForever($roleCacheKey, function () use($user) {
            return Permission::select('permissions.*')
            ->join('role_permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->where('permissions.type', $user->type)
            ->where('role_permissions.role_id', $user->role_id)
            ->get();
        });

        $permissionsByUser = cache()->rememberForever($userCacheKey, function () use($user) {
            return Permission::select('permissions.*')
            ->join('user_permissions', 'permissions.id', '=', 'user_permissions.permission_id')
            ->where('permissions.type', $user->type)
            ->where('user_permissions.user_id', $user->id)
            ->get();
        });

        return $permissionsByRole
                ->merge($permissionsByUser)
                ->unique('id')
                ->values()
                ->keyBy('identifier');
    }
}
