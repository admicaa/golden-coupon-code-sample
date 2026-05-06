<?php

use App\Models\Languages;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $catalog = require database_path('seeds/permissions/all.php');
        $languages = Languages::all();

        foreach ($catalog as $permissionKey => $required) {
            $expanded = $this->expand($permissionKey, $languages);

            foreach ($expanded as $name) {
                $permission = Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'admin',
                ]);

                foreach ($required as $requiredKey) {
                    foreach ($this->expand($requiredKey, $languages) as $requiredName) {
                        $requiredPermission = Permission::firstOrCreate([
                            'name' => $requiredName,
                            'guard_name' => 'admin',
                        ]);
                        $permission->required()->syncWithoutDetaching([$requiredPermission->id]);
                    }
                }
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Expand a permission name with the `{lang}` placeholder into one entry
     * per seeded language. Plain names pass through unchanged.
     *
     * @return array<int, string>
     */
    protected function expand(string $name, $languages): array
    {
        if (!preg_match('/\{lang\}/', $name)) {
            return [$name];
        }

        return $languages
            ->map(fn ($language) => str_replace('{lang}', $language->shortcut, $name))
            ->values()
            ->all();
    }
}
