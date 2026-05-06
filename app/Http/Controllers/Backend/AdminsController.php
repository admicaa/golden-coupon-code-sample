<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\AdminCreateRequest;
use App\Http\Requests\Backend\AdminUpdateRequest;
use App\Models\Admin;
use App\Services\Admin\AdminRoleService;
use Illuminate\Http\Request;

class AdminsController extends Controller
{
    protected $adminRoles;

    public function __construct(AdminRoleService $adminRoles)
    {
        $this->adminRoles = $adminRoles;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Admin::class);

        $perPage = per_page($request->input('itemsPerPage'));

        return Admin::paginate($perPage);
    }

    public function store(AdminCreateRequest $request)
    {
        return $this->adminRoles->create($request->validated());
    }

    public function update(AdminUpdateRequest $request, Admin $admin)
    {
        $this->authorize('update', [$admin, $request->filled('password')]);

        return $this->adminRoles->update($admin, $request->validated(), $request->file('avatar'));
    }

    public function destroy(Admin $admin)
    {
        $this->authorize('delete', $admin);
        $admin->delete();

        return $admin->id;
    }
}
