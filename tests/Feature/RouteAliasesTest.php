<?php

namespace Tests\Feature;

use App\Models\Admin;
use Tests\Concerns\RefreshMySqlDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\Concerns\InteractsWithAdminAuth;
use Tests\TestCase;

/**
 * Pins the verb-in-URL aliases the Vue admin still calls:
 *   /admins/{create,update/{admin},delete/{admin}}
 *   /roles/{create,edit/{role},delete/{role}}
 *   /mainpage/save
 */
class RouteAliasesTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshMySqlDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_legacy_admins_create_alias_still_works(): void
    {
        $this->createRole('super-admin', ['create-admins']);
        $editorRole = $this->createRole('editor', ['view-articles']);
        $this->actingAsAdminWithPermissions(
            ['create-admins'],
            ['super-admin'],
            ['email' => 'creator@example.com']
        );

        $response = $this->postJson('/api/admins/create', [
            'name' => 'Aliased Admin',
            'email' => 'aliased@example.com',
            'password' => 'secret123',
            'roles' => [['name' => $editorRole->name]],
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('admins', ['email' => 'aliased@example.com']);
    }

    public function test_legacy_admins_update_alias_still_works(): void
    {
        $this->createRole('super-admin', ['edit-admins']);
        $this->actingAsAdminWithPermissions(
            ['edit-admins'],
            ['super-admin'],
            ['email' => 'root@example.com']
        );

        $target = Admin::create([
            'name' => 'Target',
            'email' => 'target@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/admins/update/' . $target->id, [
            'name' => 'Renamed Target',
            'email' => 'target@example.com',
        ]);

        $response->assertOk();
        $this->assertSame('Renamed Target', $target->fresh()->name);
    }

    public function test_legacy_admins_delete_alias_still_works(): void
    {
        $this->createRole('super-admin', ['delete-admins']);
        $this->actingAsAdminWithPermissions(
            ['delete-admins'],
            ['super-admin'],
            ['email' => 'root2@example.com']
        );

        $target = Admin::create([
            'name' => 'Disposable',
            'email' => 'disposable@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $this->deleteJson('/api/admins/delete/' . $target->id)->assertOk();
        $this->assertDatabaseMissing('admins', ['id' => $target->id]);
    }

    public function test_legacy_roles_aliases_still_work(): void
    {
        $permission = \App\Models\Permission::firstOrCreate([
            'name' => 'view-articles',
            'guard_name' => 'admin',
        ]);

        $this->createRole('super-admin', ['create-roles', 'edit-roles', 'delete-roles']);
        $this->actingAsAdminWithPermissions(
            ['create-roles', 'edit-roles', 'delete-roles', 'view-articles'],
            ['super-admin']
        );

        $createResponse = $this->postJson('/api/roles/create', [
            'name' => 'aliased-role',
            'permissions' => [['id' => $permission->id]],
        ]);
        $createResponse->assertSuccessful();
        $roleId = $createResponse->json('id');
        $this->assertNotNull($roleId);

        $editResponse = $this->putJson('/api/roles/edit/' . $roleId, [
            'name' => 'aliased-role-renamed',
            'permissions' => [['id' => $permission->id]],
        ]);
        $editResponse->assertSuccessful();
        $this->assertDatabaseHas('roles', [
            'id' => $roleId,
            'name' => 'aliased-role-renamed',
        ]);

        $this->deleteJson('/api/roles/delete/' . $roleId)->assertOk();
        $this->assertDatabaseMissing('roles', ['id' => $roleId]);
    }

    public function test_legacy_mainpage_save_alias_still_works(): void
    {
        $this->actingAsAdminWithPermissions(['view-main-page', 'edit-main-page']);

        // 422 (not 404/403) confirms the alias resolves and reaches the FormRequest.
        $response = $this->postJson('/api/mainpage/save', [
            'sections' => [],
        ]);

        $response->assertStatus(422);
    }
}
