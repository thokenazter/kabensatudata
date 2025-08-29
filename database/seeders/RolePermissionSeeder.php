<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $roles = [
            'admin',
            'petugas',
            'supervisor',
            'kepala_puskesmas',
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }

        // Buat permissions dasar untuk masing-masing resource
        $resources = [
            'user',
            'role',
            'permission',
            // Tambahkan resource lain yang Anda miliki
        ];

        $permissions = [];

        foreach ($resources as $resource) {
            $permissions[] = "view_{$resource}_resource";
            $permissions[] = "create_{$resource}_resource";
            $permissions[] = "update_{$resource}_resource";
            $permissions[] = "delete_{$resource}_resource";
        }

        // Tambahkan permissions khusus
        $customPermissions = [
            'view_dashboard',
            'export_reports',
            'input_survey',
            'view_reports',
        ];

        $permissions = array_merge($permissions, $customPermissions);

        // Buat permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions ke roles
        $role = Role::findByName('admin');
        $role->givePermissionTo(Permission::all());

        $role = Role::findByName('petugas');
        $role->givePermissionTo([
            'view_dashboard',
            'input_survey',
        ]);

        $role = Role::findByName('supervisor');
        $role->givePermissionTo([
            'view_dashboard',
            'view_reports',
            'export_reports',
        ]);

        $role = Role::findByName('kepala_puskesmas');
        $role->givePermissionTo([
            'view_dashboard',
            'view_reports',
        ]);

        // Buat user admin jika belum ada
        if (!User::where('email', 'admin@example.com')->exists()) {
            $admin = User::create([
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);

            $admin->assignRole('admin');
        }
    }
}
