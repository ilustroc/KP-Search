<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\SearchContext;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SearchModuleControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authorized_module_returns_temporarily_unavailable_without_querying_gold(): void
    {
        $user = $this->userWithPermissions(['search.use', 'search.view_contact']);
        [$context, $token] = $this->contextFor($user);

        $response = $this->actingAs($user)->get(route('search.results.modules.show', [
            'token' => $token,
            'module' => 'contact',
        ]));

        $response->assertOk()
            ->assertSee('Módulo temporalmente no disponible')
            ->assertSee('Módulo: Contacto')
            ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-store, private');
        $this->assertDatabaseHas('audit_events', [
            'search_context_id' => $context->id,
            'event_type' => 'search.module_viewed',
            'module' => 'contact',
            'outcome' => 'unavailable',
        ]);
    }

    public function test_module_without_permission_is_forbidden_and_audited(): void
    {
        $user = $this->userWithPermissions(['search.use']);
        [$context, $token] = $this->contextFor($user);

        $response = $this->actingAs($user)->get(route('search.results.modules.show', [
            'token' => $token,
            'module' => 'financial',
        ]));

        $response->assertForbidden();
        $this->assertDatabaseHas('audit_events', [
            'search_context_id' => $context->id,
            'event_type' => 'search.module_viewed',
            'module' => 'financial',
            'outcome' => 'forbidden',
        ]);
    }

    public function test_unknown_module_returns_not_found(): void
    {
        $user = $this->userWithPermissions(['search.use']);
        [, $token] = $this->contextFor($user);

        $this->actingAs($user)
            ->get("/search/result/{$token}/modules/not-real")
            ->assertNotFound();
    }

    /**
     * @param  list<string>  $permissions
     */
    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        foreach ($permissions as $permissionName) {
            $permission = Permission::factory()->create(['name' => $permissionName]);
            $role->permissions()->attach($permission);
        }

        $user->roles()->attach($role);

        return $user;
    }

    /**
     * @return array{SearchContext, string}
     */
    private function contextFor(User $user): array
    {
        $token = str_repeat('D', 64);
        $context = SearchContext::factory()->for($user)->create([
            'token_hash' => hash('sha256', $token),
        ]);

        return [$context, $token];
    }
}
