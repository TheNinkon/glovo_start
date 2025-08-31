<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderForecastState extends Model
{
    use HasFactory;

    protected $fillable = [
        'rider_id', 'forecast_id', 'required_weekly_minutes', 'reserved_weekly_minutes', 'locked_at', 'wildcards_remaining'
    ];

    protected $casts = [
        'locked_at' => 'datetime',
    ];

    public function rider(): BelongsTo { return $this->belongsTo(Rider::class); }
    public function forecast(): BelongsTo { return $this->belongsTo(Forecast::class); }

    public function hasLock(): bool
    {
        return !is_null($this->locked_at);
    }

    public function lock(): void
    {
        if (!$this->locked_at) {
            $this->locked_at = Carbon::now();
            $this->save();
        }
    }

    public function canConsumeWildcard(): bool
    {
        return $this->wildcards_remaining > 0;
    }
}

