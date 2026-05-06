<?php

namespace App\Services\Admin;

use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminRoleService
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $admin = Admin::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $this->syncRoles($admin, $data['roles']);

            return $admin;
        });
    }

    public function update(Admin $admin, array $data, $avatar = null)
    {
        return DB::transaction(function () use ($admin, $data, $avatar) {
            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (!empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $admin->update($payload);

            if (array_key_exists('roles', $data)) {
                $this->syncRoles($admin, (array) $data['roles']);
            }

            if ($avatar) {
                $path = $avatar->store('avatars');
                $admin->update(['image_path' => url(Storage::url($path))]);
            }

            return $admin->fresh();
        });
    }

    public function syncRoles(Admin $admin, array $roles)
    {
        $admin->syncRoles($this->extractRoleNames($roles));

        return $admin;
    }

    public function extractRoleNames(array $roles)
    {
        return collect($roles)->pluck('name')->filter()->values()->all();
    }
}
