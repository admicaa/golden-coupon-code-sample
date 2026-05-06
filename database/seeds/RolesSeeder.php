<?php

use App\Models\Languages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run()
    {
        $languages = Languages::all();
        $catalog = require database_path('seeds/permissions/all.php');

        // All known permission names, with `{lang}` expanded against the
        // currently seeded languages. Anything we list for a role below has
        // to be a member of this set.
        $allPermissions = collect(array_keys($catalog))
            ->flatMap(fn ($name) => $this->expand($name, $languages))
            ->unique()
            ->values()
            ->all();

        $roles = [
            // super-admin always gets every permission, including any new ones
            // added later. This is the role assigned to the default admin.
            'super-admin' => $allPermissions,

            // Manages content end-to-end (countries, stores, coupons,
            // articles, search options, main page, translations) but cannot
            // touch admins or roles.
            'content-manager' => $this->intersect($allPermissions, array_merge(
                [
                    'edit-his-profile-data',
                    'edit-his-password',
                    'view-countries', 'edit-countries', 'create-countries', 'delete-countries',
                    'view-stores', 'create-stores', 'edit-stores', 'delete-stores',
                    'view-coupons', 'create-coupons', 'edit-coupons', 'delete-coupons',
                    'view-articles', 'create-articles', 'edit-articles', 'delete-articles',
                    'view-search-options', 'create-search-options', 'edit-search-options', 'delete-search-options', 'assign-search-options',
                    'view-main-page', 'edit-main-page',
                ],
                $this->expandList(['edit-countries-{lang}', 'edit-stores-{lang}', 'edit-coupons-{lang}', 'translate-{lang}'], $languages)
            )),

            // Read across content + visibility into who/what is configured.
            // No write access anywhere except their own profile.
            'support-admin' => $this->intersect($allPermissions, [
                'edit-his-profile-data',
                'edit-his-password',
                'view-admins',
                'view-roles',
                'view-languages',
                'view-countries',
                'view-stores',
                'view-coupons',
                'view-articles',
                'view-search-options',
                'view-main-page',
            ]),

            // Pure read-only on content. Useful for stakeholders who need
            // a peek without any edit surface.
            'viewer' => $this->intersect($allPermissions, [
                'edit-his-profile-data',
                'edit-his-password',
                'view-countries',
                'view-stores',
                'view-coupons',
                'view-articles',
                'view-search-options',
                'view-main-page',
            ]),
        ];

        foreach ($roles as $name => $permissions) {
            $role = Role::firstOrCreate([
                'guard_name' => 'admin',
                'name' => $name,
            ]);

            // Resolve names to permission models so syncPermissions does not
            // silently skip anything that has not been seeded yet.
            $models = Permission::query()
                ->where('guard_name', 'admin')
                ->whereIn('name', $permissions)
                ->get();

            $role->syncPermissions($models);
        }
    }

    /**
     * Expand `{lang}` placeholders across the seeded languages.
     *
     * @return array<int, string>
     */
    protected function expand(string $name, Collection $languages): array
    {
        if (!preg_match('/\{lang\}/', $name)) {
            return [$name];
        }

        return $languages
            ->map(fn ($language) => str_replace('{lang}', $language->shortcut, $name))
            ->values()
            ->all();
    }

    /**
     * @param array<int, string> $names
     * @return array<int, string>
     */
    protected function expandList(array $names, Collection $languages): array
    {
        return collect($names)
            ->flatMap(fn ($name) => $this->expand($name, $languages))
            ->all();
    }

    /**
     * Keep only the permission names that actually exist in `$allowed`.
     *
     * @param array<int, string> $allowed
     * @param array<int, string> $wanted
     * @return array<int, string>
     */
    protected function intersect(array $allowed, array $wanted): array
    {
        return array_values(array_intersect($allowed, $wanted));
    }
}
