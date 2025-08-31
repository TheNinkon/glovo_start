<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Forecast extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'week_start', 'week_end', 'selection_deadline_at', 'status'];
    protected $casts = ['week_start' => 'date', 'week_end' => 'date', 'selection_deadline_at' => 'datetime'];

    public function slots(): HasMany { return $this->hasMany(ForecastSlot::class); }

    public function scopePublished(Builder $query): Builder { return $query->where('status', 'published'); }
    public function scopeActiveWeek(Builder $query, Carbon $date): Builder
    {
        return $query->where('week_start', '<=', $date)->where('week_end', '>=', $date);
    }
}
