<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\SearchContext;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class SearchResultControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_view_result_shell_without_document_in_url_or_html(): void
    {
        $user = $this->userWithSearchPermission();
        $token = str_repeat('A', 64);
        SearchContext::factory()->for($user)->create([
            'token_hash' => hash('sha256', $token),
            'document_ciphertext' => '12345678',
        ]);

        $response = $this->actingAs($user)->get(route('search.results.show', ['token' => $token]));

        $response->assertOk()
            ->assertSee('Información personal')
            ->assertSee('••••5678')
            ->assertDontSee('12345678')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
        $this->assertStringNotContainsString(
            '12345678',
            route('search.results.show', ['token' => $token]),
        );
    }

    public function test_expired_context_returns_not_found(): void
    {
        $user = $this->userWithSearchPermission();
        $token = str_repeat('B', 64);
        SearchContext::factory()->expired()->for($user)->create([
            'token_hash' => hash('sha256', $token),
        ]);

        $this->actingAs($user)
            ->get(route('search.results.show', ['token' => $token]))
            ->assertNotFound();
    }

    public function test_context_owned_by_another_user_returns_not_found(): void
    {
        $owner = User::factory()->create();
        $requestingUser = $this->userWithSearchPermission();
        $token = str_repeat('C', 64);
        SearchContext::factory()->for($owner)->create([
            'token_hash' => hash('sha256', $token),
        ]);

        $this->actingAs($requestingUser)
            ->get(route('search.results.show', ['token' => $token]))
            ->assertNotFound();
    }

    public function test_invalid_token_returns_not_found(): void
    {
        $user = $this->userWithSearchPermission();

        $this->actingAs($user)
            ->get('/search/result/not-a-valid-token')
            ->assertNotFound();
    }

    private function userWithSearchPermission(): User
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['name' => 'search.use']);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $user;
    }
}
