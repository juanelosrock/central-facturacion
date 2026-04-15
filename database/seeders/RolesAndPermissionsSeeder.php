<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

		$permissions = [
			'users.view', 'users.create', 'users.edit', 'users.delete',
			'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
			'settings.view', 'settings.edit',
			'companies.view', 'companies.create', 'companies.edit', 'companies.delete', 'companies.sync',
		];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $editor = Role::firstOrCreate(['name' => 'editor']);
        $editor->syncPermissions(['users.view', 'roles.view']);

        Role::firstOrCreate(['name' => 'user']);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador',
                'password' => 'password', // se hashea automáticamente
            ]
        );
        $adminUser->assignRole('admin');
    }
}