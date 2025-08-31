<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // El orden es importante: primero roles, luego los datos que dependen de ellos.
        $this->call([
            RolesAndPermissionsSeeder::class,
            ModelDataSeeder::class, // Este ya crea Riders
            AccountSeeder::class,   // Este crea Accounts y Assignments
            ForecastSeeder::class,
            QuickDemoSeeder::class, // Ajusta horas contratadas y crea forecast+slots de esta semana
        ]);
    }
}
