<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForecastSlot extends Model
{
    use HasFactory;
    protected $fillable = ['forecast_id', 'date', 'start_time', 'end_time', 'capacity'];
    protected $casts = ['date' => 'date'];

    public function forecast(): BelongsTo { return $this->belongsTo(Forecast::class); }
    public function riderSchedules(): HasMany { return $this->hasMany(RiderSchedule::class); }

    public function getAssignedCountAttribute(): int
    {
        // Si la relación ya está cargada, la usamos. Si no, la cargamos.
        if ($this->relationLoaded('riderSchedules')) {
            return $this->riderSchedules->where('status', 'reserved')->count();
        }
        return $this->riderSchedules()->where('status', 'reserved')->count();
    }
}
