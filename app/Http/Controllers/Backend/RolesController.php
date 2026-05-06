<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\RoleRequest;
use App\Models\Permission;
use App\Services\Admin\RolePermissionService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    protected $roles;

    public function __construct(RolePermissionService $roles)
    {
        $this->roles = $roles;
    }

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

        return $this->roles->create($request->validated());
    }

    public function update(RoleRequest $request, Role $role)
    {
        $this->authorize('update', $role);

        return $this->roles->update($role, $request->validated());
    }

    public function destroy(Role $role)
    {
        $this->authorize('delete', $role);
        $role->delete();

        return $role->id;
    }
}
