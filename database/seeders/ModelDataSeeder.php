<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Assignment;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ModelDataSeeder extends Seeder
{
    public function run(): void
    {
        // Creamos Supervisores (que también son Riders)
        $supervisors = Rider::factory(2)->create(['status' => 'active']);

        // Creamos Riders normales
        Rider::factory(10)->create([
            'supervisor_id' => $supervisors->random()->id,
        ]);

        // Para CADA Rider, creamos una cuenta de User y la vinculamos
        Rider::all()->each(function ($rider) {
            $user = User::create([
                'name' => $rider->name,
                'email' => $rider->email,
                'password' => Hash::make('password123'),
            ]);

            $user->assignRole('rider');
            $rider->user_id = $user->id;
            $rider->save();
        });

        // Creamos Cuentas y Asignaciones
        $accounts = Account::factory(15)->create();
        $riders = Rider::whereNotNull('supervisor_id')->get(); // Solo riders normales

        foreach ($riders as $rider) {
            if ($accounts->where('status', 'active')->count() >= 2) {
                $accountsToAssign = $accounts->where('status', 'active')->random(2);
                foreach ($accountsToAssign as $account) {
                    Assignment::factory()->create([
                        'rider_id' => $rider->id,
                        'account_id' => $account->id,
                    ]);
                }
            }
        }
    }
}
