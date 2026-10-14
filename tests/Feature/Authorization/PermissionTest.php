<?php

namespace Tests\Feature\Authorization;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_permission_catalogue_contains_the_approved_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->assertSame([
            'search.audit',
            'search.export',
            'search.use',
            'search.view_addresses',
            'search.view_contact',
            'search.view_family',
            'search.view_financial',
            'search.view_labor',
            'search.view_properties',
            'search.view_tax',
            'search.view_vehicles',
        ], Permission::query()->orderBy('name')->pluck('name')->all());
    }

    public function test_administrator_and_systems_roles_receive_every_permission(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $permissions = Permission::query()->pluck('name');

        foreach (['administrador', 'sistemas'] as $roleName) {
            $user = $this->userWithRole($roleName);

            foreach ($permissions as $permission) {
                $this->assertTrue(Gate::forUser($user)->allows($permission), "{$roleName} lacks {$permission}");
            }
        }
    }

    public function test_advisor_permissions_follow_least_privilege_matrix(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = $this->userWithRole('asesor');

        $this->assertTrue($user->can('search.use'));
        $this->assertTrue($user->can('search.view_contact'));
        $this->assertTrue($user->can('search.view_addresses'));
        $this->assertTrue($user->can('search.view_family'));
        $this->assertTrue($user->can('search.view_labor'));
        $this->assertFalse($user->can('search.view_vehicles'));
        $this->assertFalse($user->can('search.view_tax'));
        $this->assertFalse($user->can('search.view_financial'));
        $this->assertFalse($user->can('search.view_properties'));
        $this->assertFalse($user->can('search.export'));
        $this->assertFalse($user->can('search.audit'));
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $role = Role::query()->where('name', $roleName)->firstOrFail();
        $user->roles()->attach($role);

        return $user;
    }
}
