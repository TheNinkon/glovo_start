<?php

namespace Database\Seeders;

// --- AÑADE ESTAS LÍNEAS DE IMPORTACIÓN ---
use App\Models\Forecast;
use App\Models\ForecastSlot;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
// --- FIN DE LÍNEAS A AÑADIR ---

class ForecastSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startOfWeek = Carbon::now()->startOfWeek();

        $forecast = Forecast::create([
            'name' => 'Week ' . $startOfWeek->format('W (Y)'),
            'week_start' => $startOfWeek,
            'week_end' => $startOfWeek->copy()->endOfWeek(),
            'selection_deadline_at' => $startOfWeek->copy()->subDays(2),
            'status' => 'published',
        ]);

        // Crear slots de 8-12, 12-16, 16-20, 20-24 para 3 días
        for ($i = 0; $i < 3; $i++) {
            $date = $startOfWeek->copy()->addDays($i);
            $times = ['08:00:00', '12:00:00', '16:00:00', '20:00:00'];
            foreach ($times as $startTime) {
                ForecastSlot::create([
                    'forecast_id' => $forecast->id,
                    'date' => $date,
                    'start_time' => $startTime,
                    'end_time' => Carbon::parse($startTime)->addHours(4)->format('H:i:s'),
                    'capacity' => rand(2, 5),
                ]);
            }
        }
    }
}
