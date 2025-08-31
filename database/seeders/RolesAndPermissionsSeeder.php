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

        // Define permissions used across the app
        $permissions = [
            // Forecasts
            'forecasts.view',
            'forecasts.create',
            'forecasts.upload',
            'forecasts.manage',
            // Rider schedules
            'schedules.reserve',
            'schedules.cancel',
            'schedules.commit',
            // Wildcards
            'wildcards.grant',
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Definir los roles
        $roles = [
            'super-admin',
            'zone-manager',
            'support',
            'finance',
            'rider'
        ];

        // Crear los roles
        $roleModels = [];
        foreach ($roles as $roleName) {
            $roleModels[$roleName] = Role::firstOrCreate(['name' => $roleName]);
        }

        // Assign permissions to roles
        // super-admin gets all permissions
        $roleModels['super-admin']->givePermissionTo($permissions);
        // zone-manager: full forecasts + wildcards
        $roleModels['zone-manager']->givePermissionTo([
            'forecasts.view','forecasts.create','forecasts.upload','forecasts.manage','wildcards.grant'
        ]);
        // support: can view and upload forecasts
        $roleModels['support']->givePermissionTo(['forecasts.view','forecasts.upload']);
        // finance: can view and upload forecasts
        $roleModels['finance']->givePermissionTo(['forecasts.view','forecasts.upload']);
        // rider: schedule actions
        $roleModels['rider']->givePermissionTo(['schedules.reserve','schedules.cancel','schedules.commit']);

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
