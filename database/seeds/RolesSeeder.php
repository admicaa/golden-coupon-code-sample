<?php

use App\Models\Languages;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissionsAll = require database_path('seeds/permissions/all.php');
        $permissions = [];
        $languages = Languages::all();
        foreach ($permissionsAll as $permission => $required) {
            if (preg_match('/\{lang\}/', $permission)) {
                foreach ($languages as $lang) {
                    $permissions[str_replace('{lang}', $lang->shortcut, $permission)] = [];
                }
            } else {
                $permissions[$permission] = [];
            }
        }
        $rules = [
            'super-admin' => $permissions
        ];

        foreach ($rules as $rule => $rulePermissions) {
            $role = Role::firstOrCreate(['guard_name' => 'admin', 'name' => $rule]);
            $role->syncPermissions(array_keys($rulePermissions));
        }
    }
}
