<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definir los roles
        $roles = [
            'super-admin',
            'zone-manager',
            'support',
            'finance',
            'rider'
        ];

        // Crear los roles
        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName]);
        }

        // Crear un usuario Super Admin
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password123'),
        ]);
        $superAdmin->assignRole('super-admin');

        // Crear un usuario Zone Manager
        $zoneManager = User::factory()->create([
            'name' => 'Zone Manager',
            'email' => 'zonemanager@example.com',
            'password' => Hash::make('password123'),
        ]);
        $zoneManager->assignRole('zone-manager');

        // Crear un usuario Support
        $supportUser = User::factory()->create([
            'name' => 'Support User',
            'email' => 'support@example.com',
            'password' => Hash::make('password123'),
        ]);
        $supportUser->assignRole('support');

        // Crear un usuario Finance
        $financeUser = User::factory()->create([
            'name' => 'Finance User',
            'email' => 'finance@example.com',
            'password' => Hash::make('password123'),
        ]);
        $financeUser->assignRole('finance');

        // Crear un usuario Rider
        $riderUser = User::factory()->create([
            'name' => 'Rider User',
            'email' => 'rider@example.com',
            'password' => Hash::make('password123'),
        ]);
        $riderUser->assignRole('rider');
    }
}
