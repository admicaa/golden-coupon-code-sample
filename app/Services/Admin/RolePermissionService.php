<?php

namespace App\Services\Admin;

use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RolePermissionService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => 'admin',
            ]);

            $this->syncPermissions($role, $data['permissions']);

            return $role;
        });
    }

    public function update(Role $role, array $data)
    {
        return DB::transaction(function () use ($role, $data) {
            $role->update(['name' => $data['name']]);

            return $this->syncPermissions($role, $data['permissions']);
        });
    }

    public function syncPermissions(Role $role, array $permissions)
    {
        $role->syncPermissions($this->expandPermissionNames($permissions));

        return $role->fresh('permissions');
    }

    public function expandPermissionNames(array $permissions)
    {
        $permissionIds = collect($permissions)->pluck('id')->filter()->unique()->values()->all();
        $expanded = [];
        $visited = [];

        foreach (Permission::query()->whereIn('id', $permissionIds)->get() as $permission) {
            $this->appendPermission($permission, $expanded, $visited);
        }

        return array_values(array_unique($expanded));
    }

    protected function appendPermission(Permission $permission, array &$expanded, array &$visited)
    {
        if (isset($visited[$permission->id])) {
            return;
        }

        $visited[$permission->id] = true;
        $expanded[] = $permission->name;

        foreach ($permission->required as $required) {
            $this->appendPermission($required, $expanded, $visited);
        }
    }
}
