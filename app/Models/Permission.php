<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $visible = ['id', 'name', 'required'];
    protected $with = ['required'];
    public function required()
    {
        return $this->belongsToMany(Permission::class, 'permission_requirements', 'permission_id', 'required_permission_id');
    }
}
