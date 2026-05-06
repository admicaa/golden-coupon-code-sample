<?php

use App\Models\Languages;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{



    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $permissions = require database_path('seeds/permissions/all.php');
        $languages = Languages::all();
        foreach ($permissions as $permission => $required) {
            if (preg_match('/\{lang\}/', $permission)) {
                foreach ($languages as $language) {
                    $permissione = Permission::firstOrCreate([
                        'name' => str_replace('{lang}', $language->shortcut, $permission),
                        'guard_name' => 'admin'
                    ]);
                    foreach ($required as $requiredPermission) {
                        $requiredPermission = Permission::firstOrCreate([
                            'name' => $requiredPermission,
                            'guard_name' => 'admin'
                        ]);
                        $permissione->required()->syncWithoutDetaching([$requiredPermission->id]);
                    }
                }
                continue;
            }

            $permission = Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'admin'
            ]);
            foreach ($required as $requiredPermission) {
                $requiredPermission = Permission::firstOrCreate([
                    'name' => $requiredPermission,
                    'guard_name' => 'admin'
                ]);
                $permission->required()->syncWithoutDetaching([$requiredPermission->id]);
            }
        }
    }
}
