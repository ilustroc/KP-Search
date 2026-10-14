<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionLabels = [
            'search.use' => 'Usar el buscador',
            'search.view_contact' => 'Ver información de contacto',
            'search.view_addresses' => 'Ver direcciones',
            'search.view_family' => 'Ver información familiar',
            'search.view_labor' => 'Ver información laboral',
            'search.view_vehicles' => 'Ver vehículos',
            'search.view_tax' => 'Ver información SUNAT',
            'search.view_financial' => 'Ver información SBS',
            'search.view_properties' => 'Ver propiedades',
            'search.export' => 'Exportar información',
            'search.audit' => 'Consultar auditoría',
        ];

        $rolePermissions = [
            'administrador' => array_keys($permissionLabels),
            'sistemas' => array_keys($permissionLabels),
            'supervisor' => [
                'search.use',
                'search.view_contact',
                'search.view_addresses',
                'search.view_family',
                'search.view_labor',
                'search.view_vehicles',
                'search.view_tax',
                'search.view_financial',
                'search.view_properties',
                'search.audit',
            ],
            'asesor' => [
                'search.use',
                'search.view_contact',
                'search.view_addresses',
                'search.view_family',
                'search.view_labor',
            ],
        ];

        DB::transaction(function () use ($permissionLabels, $rolePermissions): void {
            foreach ($permissionLabels as $name => $label) {
                Permission::query()->updateOrCreate(['name' => $name], ['label' => $label]);
            }

            foreach ($rolePermissions as $roleName => $permissions) {
                $role = Role::query()->updateOrCreate(
                    ['name' => $roleName],
                    ['label' => ucfirst($roleName)],
                );

                $role->permissions()->sync(
                    Permission::query()->whereIn('name', $permissions)->pluck('id'),
                );
            }
        });
    }
}
