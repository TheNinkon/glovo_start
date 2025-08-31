<?php

namespace App\Http\Controllers\ZoneManager;

use App\Http\Controllers\Controller;
use App\Models\RiderForecastState;
use Illuminate\Http\Request;

class WildcardController extends Controller
{
    public function store(Request $request)
    {
        $this->middleware(['auth']);
        $request->validate([
            'rider_id' => ['required', 'exists:riders,id'],
            'forecast_id' => ['required', 'exists:forecasts,id'],
            'tokens' => ['required', 'integer', 'min:1']
        ]);

        $state = RiderForecastState::firstOrCreate(
            ['rider_id' => $request->integer('rider_id'), 'forecast_id' => $request->integer('forecast_id')],
            [
                'required_weekly_minutes' => 0,
                'reserved_weekly_minutes' => 0,
                'wildcards_remaining' => config('scheduling.default_wildcards_per_forecast', 5),
            ]
        );
        $state->wildcards_remaining += $request->integer('tokens');
        $state->save();

        return response()->json(['success' => true, 'message' => 'Wildcards granted.', 'data' => ['wildcards_remaining' => $state->wildcards_remaining]]);
    }
}

