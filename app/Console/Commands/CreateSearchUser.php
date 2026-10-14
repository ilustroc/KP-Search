<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

#[Signature('search:user:create {email : Corporate email} {--name= : Full name} {--role=asesor : Assigned role}')]
#[Description('Create a private KP SEARCH user without enabling public registration')]
class CreateSearchUser extends Command
{
    public function handle(): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));
        $name = trim((string) ($this->option('name') ?: $this->ask('Nombre completo')));
        $roleName = (string) $this->option('role');
        $password = (string) $this->secret('Contraseña (mínimo 12 caracteres)');
        $passwordConfirmation = (string) $this->secret('Confirmar contraseña');

        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'role' => $roleName,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'exists:roles,name'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::INVALID;
        }

        DB::transaction(function () use ($email, $name, $password, $roleName): void {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'is_active' => true,
            ]);
            $role = Role::query()->where('name', $roleName)->firstOrFail();
            $user->roles()->attach($role);
        });

        $this->info("User {$email} created with role {$roleName}.");

        return self::SUCCESS;
    }
}
