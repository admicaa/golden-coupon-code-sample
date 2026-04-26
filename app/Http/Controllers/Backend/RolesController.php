<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\RoleRequest;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        $perPage = per_page($request->input('itemsPerPage'));
        $with = $request->boolean('onlyRoles') ? [] : ['permissions'];

        return Role::with($with)->paginate($perPage);
    }

    public function permissions()
    {
        $this->authorize('viewAny', Role::class);

        return Permission::all();
    }

    public function store(RoleRequest $request)
    {
        $this->authorize('create', Role::class);

        return DB::transaction(function () use ($request) {
            $role = Role::create([
                'name' => $request->input('name'),
                'guard_name' => 'admin',
            ]);

            foreach ($request->input('permissions') as $permission) {
                $this->addPermissionToRole(Permission::find($permission['id']), $role);
            }

            return $role;
        });
    }

    public function update(RoleRequest $request, Role $role)
    {
        $this->authorize('update', $role);

        return DB::transaction(function () use ($request, $role) {
            $role->update(['name' => $request->input('name')]);
            $role->permissions()->sync([]);

            foreach ($request->input('permissions') as $permission) {
                $this->addPermissionToRole(Permission::find($permission['id']), $role);
            }

            return $role->fresh('permissions');
        });
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);
        $role->delete();

        return $role->id;
    }

    protected function addPermissionToRole(Permission $permission, Role $role)
    {
        $role->givePermissionTo($permission->name);
        foreach ($permission->required as $required) {
            $this->addPermissionToRole($required, $role);
        }
    }
}
