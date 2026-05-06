<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasApiTokens;
    use HasRoles;
    use Notifiable;

    /** Spatie\Permission scopes roles & permissions to the `admin` guard. */
    protected $guard_name = 'admin';

    /** @var array<int, string> */
    protected $fillable = [
        'name', 'email', 'password', 'image_path',
    ];

    /** @var array<int, string> */
    protected $hidden = [
        'password', 'remember_token', 'permissions', 'image_path',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['type', 'all_permissions', 'image'];


    public function getAllPermissionsAttribute()
    {
        $permissions = $this->getAllPermissions();
        return $permissions->map(function ($permission) {
            return $permission->name;
        });
    }

    public function getTypeAttribute()
    {
        return 'admin';
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'admin_id', 'id');
    }

    public function getImageAttribute()
    {
        return $this->image_path ?: '/images/user.svg';
    }
}
