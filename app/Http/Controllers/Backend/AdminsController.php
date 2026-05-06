<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\AdminCreateRequest;
use App\Http\Requests\Backend\AdminUpdateRequest;
use App\Models\Admin;
use App\Services\Admin\AdminRoleService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AdminsController extends Controller
{
    public function __construct(
        protected AdminRoleService $adminRoles,
    ) {
    }

    public function index(Request $request): LengthAwarePaginator
    {
        $this->authorize('viewAny', Admin::class);

        return Admin::paginate(per_page($request->input('itemsPerPage')));
    }

    public function store(AdminCreateRequest $request)
    {
        return $this->adminRoles->create($request->validated());
    }

    public function update(AdminUpdateRequest $request, Admin $admin)
    {
        // Role-escalation guards live in AdminUpdateRequest::authorize().
        return $this->adminRoles->update($admin, $request->validated(), $request->file('avatar'));
    }

    public function destroy(Admin $admin)
    {
        $this->authorize('delete', $admin);
        $admin->delete();

        return $admin->id;
    }
}
