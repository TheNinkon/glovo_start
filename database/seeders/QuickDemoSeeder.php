<?php

namespace Database\Seeders;

use App\Models\Forecast;
use App\Models\ForecastSlot;
use App\Models\Rider;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class QuickDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Set default contract hours for all riders to 30h if not set
        Rider::whereNull('contract_hours_per_week')->orWhere('contract_hours_per_week', 0)->update([
            'contract_hours_per_week' => 30,
        ]);
        // Default all riders to GRO if no city set yet
        Rider::whereNull('city_code')->update(['city_code' => 'GRO']);

        // 2) Ensure there is a published forecast for the current week with slots
        $today = Carbon::now();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();

        $forecast = Forecast::firstOrCreate(
            ['week_start' => $weekStart->toDateString()],
            [
                'name' => 'Week ' . $weekStart->format('W (Y)'),
                'week_end' => $weekEnd->toDateString(),
                // Set deadline in the near future so riders can select
                'selection_deadline_at' => $today->copy()->addDays(2),
                'status' => 'published',
                'city_code' => 'GRO',
            ]
        );

        // 3) Create 30-min slots 10:00–22:00 for each day (capacity 3)
        for ($d = 0; $d < 7; $d++) {
            $date = $weekStart->copy()->addDays($d)->toDateString();
            $start = Carbon::parse($date . ' 10:00:00');
            $end = Carbon::parse($date . ' 22:00:00');

            $cursor = $start->copy();
            while ($cursor < $end) {
                $slotStart = $cursor->format('H:i:s');
                $slotEnd = $cursor->copy()->addMinutes(30)->format('H:i:s');

                ForecastSlot::firstOrCreate([
                    'forecast_id' => $forecast->id,
                    'date' => $date,
                    'start_time' => $slotStart,
                    'end_time' => $slotEnd,
                ], [
                    'capacity' => 3,
                ]);

                $cursor->addMinutes(30);
            }
        }
    }
}
