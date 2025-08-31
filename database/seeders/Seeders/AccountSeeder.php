<?php
namespace Database\Seeders;

use App\Models\Account;
use App\Models\Assignment;
use App\Models\Rider;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        Account::factory(20)->create();
        $riders = Rider::all();
        $accounts = Account::where('status', 'active')->get();

        // Crear una asignación activa para 5 cuentas
        foreach ($accounts->take(5) as $account) {
            Assignment::factory()->create([
                'account_id' => $account->id,
                'rider_id' => $riders->random()->id,
                'start_date' => now()->subDays(rand(5, 30)),
                'end_date' => null, // Asignación activa
            ]);
        }
    }
}
