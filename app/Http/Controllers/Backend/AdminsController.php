<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\AdminCreateRequest;
use App\Http\Requests\ProfileSettingsRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Admin::class);

        $perPage = per_page($request->input('itemsPerPage'));

        return Admin::paginate($perPage);
    }

    public function store(AdminCreateRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            $admin = Admin::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $this->syncRoles($data['roles'], $admin);

            return $admin;
        });
    }

    public function update(ProfileSettingsRequest $request, Admin $admin)
    {
        $payload = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ];

        if ($request->filled('password')) {
            $payload['password'] = Hash::make($request->input('password'));
        }

        DB::transaction(function () use ($request, $admin, $payload) {
            $admin->update($payload);

            if ($request->filled('roles')) {
                $this->syncRoles($request->input('roles'), $admin);
            }

            $file = $request->file('avatar');
            if ($file) {
                $path = $file->store('avatars');
                $admin->update(['image_path' => url(Storage::url($path))]);
            }
        });

        return $admin->fresh();
    }

    public function destroy(Admin $admin)
    {
        $this->authorize('delete', $admin);
        $admin->delete();

        return $admin->id;
    }

    protected function syncRoles(array $roles, Admin $admin)
    {
        $admin->roles()->sync([]);
        foreach ($roles as $role) {
            $admin->assignRole($role['name']);
        }
    }
}
