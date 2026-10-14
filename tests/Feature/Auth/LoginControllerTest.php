<?php

namespace Tests\Feature\Auth;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_can_view_the_private_login_screen(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('Iniciar sesión')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet')
            ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-store, private');
    }

    public function test_valid_credentials_authenticate_an_active_user_and_create_an_audit_event(): void
    {
        $user = User::factory()->create([
            'email' => 'user@kpinvest.local',
            'password' => 'correct-password',
        ]);

        $response = $this->post('/login', [
            'email' => 'user@kpinvest.local',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect(route('search.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertDatabaseHas('audit_events', [
            'user_id' => $user->id,
            'event_type' => 'authentication.login',
            'outcome' => 'success',
        ]);
    }

    public function test_invalid_credentials_are_rejected_and_audited_without_exposing_the_email(): void
    {
        User::factory()->create([
            'email' => 'user@kpinvest.local',
            'password' => 'correct-password',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'user@kpinvest.local',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors([
                'email' => 'Las credenciales proporcionadas no son válidas o la cuenta está inactiva.',
            ]);
        $this->assertGuest();

        $auditEvent = AuditEvent::query()->sole();
        $this->assertSame('denied', $auditEvent->outcome);
        $this->assertStringNotContainsString('user@kpinvest.local', json_encode($auditEvent->toArray()));
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        User::factory()->inactive()->create([
            'email' => 'inactive@kpinvest.local',
            'password' => 'correct-password',
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'inactive@kpinvest.local',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect('/login')->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_public_registration_route_does_not_exist(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }
}
