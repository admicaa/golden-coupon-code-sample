<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{

    use Notifiable, HasMultiAuthApiTokens, HasRoles;
    protected $guard_name = 'admin';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'image_path'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'permissions', 'image_path'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
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
