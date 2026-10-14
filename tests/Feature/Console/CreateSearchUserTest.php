<?php

namespace Tests\Feature\Console;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class CreateSearchUserTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_creates_private_user_with_selected_role_and_hashed_password(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $this->artisan('search:user:create', [
            'email' => 'admin@kpinvest.local',
            '--name' => 'Administrador KP',
            '--role' => 'administrador',
        ])
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'StrongPassword!2026')
            ->expectsQuestion('Confirmar contraseña', 'StrongPassword!2026')
            ->assertSuccessful();

        $user = User::query()->where('email', 'admin@kpinvest.local')->firstOrFail();
        $this->assertTrue($user->is_active);
        $this->assertTrue($user->roles->contains('name', 'administrador'));
        $this->assertNotSame('StrongPassword!2026', $user->getRawOriginal('password'));
    }

    public function test_rejects_unknown_role(): void
    {
        Role::factory()->create(['name' => 'asesor']);

        $this->artisan('search:user:create', [
            'email' => 'user@kpinvest.local',
            '--name' => 'Usuario KP',
            '--role' => 'unknown',
        ])
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'StrongPassword!2026')
            ->expectsQuestion('Confirmar contraseña', 'StrongPassword!2026')
            ->assertExitCode(2);

        $this->assertDatabaseCount('users', 0);
    }
}
