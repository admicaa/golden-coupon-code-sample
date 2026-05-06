<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\InteractsWithAdminAuth;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_non_super_admin_cannot_change_roles()
    {
        $editorRole = $this->createRole('editor', ['view-admins']);
        $managerRole = $this->createRole('manager', ['view-admins', 'edit-admins']);

        $this->actingAsAdminWithPermissions(['edit-admins']);
        $target = Admin::create([
            'name' => 'Target Admin',
            'email' => 'target@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $target->assignRole($editorRole);

        $response = $this->postJson('/api/admins/update/' . $target->id, [
            'name' => 'Target Admin',
            'email' => 'target@example.com',
            'roles' => [
                ['name' => $managerRole->name],
            ],
        ]);

        $response->assertStatus(403);
        $this->assertTrue($target->fresh()->hasRole('editor'));
        $this->assertFalse($target->fresh()->hasRole('manager'));
    }

    public function test_non_super_admin_cannot_edit_higher_privilege_admin()
    {
        $this->createRole('senior-manager', ['edit-admins', 'delete-admins']);

        $this->actingAsAdminWithPermissions(['edit-admins']);

        $target = Admin::create([
            'name' => 'Privileged Admin',
            'email' => 'privileged@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $target->assignRole('senior-manager');

        $response = $this->postJson('/api/admins/update/' . $target->id, [
            'name' => 'Blocked Update',
            'email' => 'privileged@example.com',
        ]);

        $response->assertStatus(403);
        $this->assertSame('Privileged Admin', $target->fresh()->name);
    }

    public function test_non_super_admin_cannot_delete_higher_privilege_admin()
    {
        $this->createRole('senior-manager', ['delete-admins', 'view-admins']);

        $this->actingAsAdminWithPermissions(['delete-admins']);

        $target = Admin::create([
            'name' => 'Delete Protected Admin',
            'email' => 'delete-protected@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $target->assignRole('senior-manager');

        $response = $this->deleteJson('/api/admins/delete/' . $target->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('admins', ['id' => $target->id]);
    }

    public function test_super_admin_role_updates_are_synced()
    {
        $this->createRole('super-admin', ['edit-admins', 'edit-admins-passwords']);
        $editorRole = $this->createRole('editor', ['view-admins']);
        $managerRole = $this->createRole('manager', ['view-admins', 'edit-admins']);

        $actor = $this->actingAsAdminWithPermissions(
            ['edit-admins', 'edit-admins-passwords'],
            ['super-admin'],
            ['email' => 'root@example.com']
        );

        $target = Admin::create([
            'name' => 'Managed Admin',
            'email' => 'managed@example.com',
            'password' => bcrypt('secret123'),
        ]);
        $target->assignRole($editorRole);

        $response = $this->postJson('/api/admins/update/' . $target->id, [
            'name' => 'Managed Admin',
            'email' => 'managed@example.com',
            'roles' => [
                ['name' => $managerRole->name],
            ],
        ]);

        $response->assertOk();
        $this->assertTrue($target->fresh()->hasRole('manager'));
        $this->assertFalse($target->fresh()->hasRole('editor'));
        $this->assertTrue($actor->fresh()->hasRole('super-admin'));
    }

    public function test_admin_can_update_own_profile_without_admin_management_permission()
    {
        $admin = $this->actingAsAdminWithPermissions(['edit-his-profile-data', 'edit-his-password']);

        $response = $this->postJson('/api/admins/update/' . $admin->id, [
            'name' => 'Updated Self',
            'email' => 'updated-self@example.com',
            'password' => 'secret1234',
        ]);

        $response->assertOk()
            ->assertJsonPath('name', 'Updated Self')
            ->assertJsonPath('email', 'updated-self@example.com');
    }
}
