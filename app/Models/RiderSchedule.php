<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiderSchedule extends Model
{
    use HasFactory;
    protected $fillable = ['rider_id', 'forecast_slot_id', 'status'];

    public function rider(): BelongsTo { return $this->belongsTo(Rider::class); }
    public function forecastSlot(): BelongsTo { return $this->belongsTo(ForecastSlot::class); }
}
