<?php

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SearchContext;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/search')->assertRedirect(route('login'));
        $this->post('/search', ['document' => '12345678'])->assertRedirect(route('login'));
    }

    public function test_user_without_search_permission_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/search')->assertForbidden();
    }

    #[DataProvider('invalidDocuments')]
    public function test_invalid_document_is_rejected(string $document): void
    {
        $user = $this->userWithPermissions(['search.use']);

        $response = $this->actingAs($user)
            ->from('/search')
            ->post('/search', ['document' => $document]);

        $response->assertRedirect('/search')->assertSessionHasErrors([
            'document' => 'El documento debe contener exactamente 8 dígitos para DNI u 11 dígitos para RUC.',
        ]);
        $this->assertDatabaseCount('search_contexts', 0);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidDocuments(): array
    {
        return [
            'DNI with seven digits' => ['1234567'],
            'DNI with nine digits' => ['123456789'],
            'RUC with ten digits' => ['1234567890'],
            'RUC with twelve digits' => ['123456789012'],
            'letters' => ['1234ABCD'],
            'spaces' => ['12 345 678'],
        ];
    }

    #[DataProvider('validDocuments')]
    public function test_valid_document_creates_encrypted_context_token_and_hmac_audit(string $document, string $type): void
    {
        $user = $this->userWithPermissions(['search.use']);

        $response = $this->actingAs($user)->post('/search', ['document' => $document]);

        $response->assertStatus(303);
        $location = (string) $response->headers->get('Location');
        $this->assertStringNotContainsString($document, $location);
        $this->assertMatchesRegularExpression('#/search/result/[A-Za-z0-9]{64}\z#', $location);

        $searchContext = SearchContext::query()->sole();
        $this->assertSame($type, $searchContext->document_type);
        $this->assertSame($document, $searchContext->document_ciphertext);
        $this->assertStringNotContainsString($document, (string) $searchContext->getRawOriginal('document_ciphertext'));
        $this->assertSame(
            hash_hmac('sha256', $document, (string) config('search.audit_hmac_key')),
            $searchContext->document_hmac,
        );
        $this->assertNotSame(hash('sha256', $document), $searchContext->document_hmac);

        $auditEvent = AuditEvent::query()->sole();
        $this->assertSame('search.created', $auditEvent->event_type);
        $this->assertSame($searchContext->document_hmac, $auditEvent->document_hmac);
        $this->assertStringNotContainsString($document, json_encode($auditEvent->toArray()));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function validDocuments(): array
    {
        return [
            'DNI' => ['12345678', 'DNI'],
            'RUC' => ['20123456789', 'RUC'],
        ];
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
}
