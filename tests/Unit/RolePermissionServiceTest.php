<?php

namespace Tests\Unit;

use App\Models\Permission;
use App\Services\Admin\RolePermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_create_expands_required_permissions_without_duplicates()
    {
        $required = Permission::create([
            'name' => 'view-stores',
            'guard_name' => 'admin',
        ]);
        $primary = Permission::create([
            'name' => 'edit-stores',
            'guard_name' => 'admin',
        ]);
        $primary->required()->attach($required->id);

        $service = $this->app->make(RolePermissionService::class);
        $role = $service->create([
            'name' => 'store-manager',
            'permissions' => [
                ['id' => $primary->id],
                ['id' => $required->id],
            ],
        ]);

        $this->assertInstanceOf(Role::class, $role);
        $this->assertSame(
            ['edit-stores', 'view-stores'],
            $role->fresh('permissions')->permissions->pluck('name')->sort()->values()->all()
        );
    }

    public function test_update_replaces_previous_permissions_with_the_expanded_new_set()
    {
        $old = Permission::create([
            'name' => 'view-coupons',
            'guard_name' => 'admin',
        ]);
        $required = Permission::create([
            'name' => 'view-articles',
            'guard_name' => 'admin',
        ]);
        $primary = Permission::create([
            'name' => 'edit-articles',
            'guard_name' => 'admin',
        ]);
        $primary->required()->attach($required->id);

        $role = Role::create([
            'name' => 'editor',
            'guard_name' => 'admin',
        ]);
        $role->givePermissionTo($old->name);

        $service = $this->app->make(RolePermissionService::class);
        $updated = $service->update($role, [
            'name' => 'content-editor',
            'permissions' => [
                ['id' => $primary->id],
            ],
        ]);

        $this->assertSame('content-editor', $updated->name);
        $this->assertSame(
            ['edit-articles', 'view-articles'],
            $updated->permissions->pluck('name')->sort()->values()->all()
        );
        $this->assertFalse($updated->hasPermissionTo('view-coupons'));
    }
}
